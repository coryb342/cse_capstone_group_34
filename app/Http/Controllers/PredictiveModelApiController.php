<?php

namespace App\Http\Controllers;

use App\Models\PredictiveModel;
use App\Models\PredictiveModelAccessToken;
use App\Models\PredictiveModelRunResult;
use App\Services\PredictiveModelApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class PredictiveModelApiController extends Controller
{
    private const API_RATE_LIMIT_PER_MIN = 2;
    private const API_RATE_LIMIT_KEY_PREFIX = 'api-hit:';
    public function __construct(
        protected PredictiveModelApiService $api_service
    )
    {
    }

    public function describeModel(Request $request): JsonResponse
    {
        $source_ip = $request->ip();
        $rate_limit_key = self::API_RATE_LIMIT_KEY_PREFIX . $source_ip;

        if (RateLimiter::tooManyAttempts($rate_limit_key, $perMinute = self::API_RATE_LIMIT_PER_MIN)) {
            $this->api_service->logApiHit($source_ip, 'describe', '429');
            return response()->json(['error' => 'Too many attempts.'], 429);
        }

        $validated_token = $this->tokenResolver($request);

        if (!$validated_token) {
            $this->api_service->logApiHit($source_ip, 'describe', '401');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user_id = $validated_token->user_id;
        $access_token_id = $validated_token->id;

        $predictive_model_id = $validated_token->model_id;
        $response = $this->api_service->describe($predictive_model_id);
        RateLimiter::hit($rate_limit_key, 60);

        if (!$response) {
            $this->api_service->logApiHit($source_ip, 'describe', '404', $user_id, $access_token_id);
            return response()->json(['error' => 'Not Found'], 404);
        }

        $this->api_service->logApiHit($source_ip, 'describe', '200', $user_id, $access_token_id);
        return response()->json($response, 200);
    }

    public function executePrediction(Request $request): JsonResponse
    {
        $source_ip = $request->ip();
        $rate_limit_key = self::API_RATE_LIMIT_KEY_PREFIX . $source_ip;

        if (RateLimiter::tooManyAttempts($rate_limit_key, $perMinute = self::API_RATE_LIMIT_PER_MIN)) {
            $this->api_service->logApiHit($source_ip, 'execute', '429');
            return response()->json(['error' => 'Too many attempts.'], 429);
        }

        $validated_token = $this->tokenResolver($request);

        if (!$validated_token) {
            $this->api_service->logApiHit($source_ip, 'execute', '401');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $predictive_model_id = $validated_token->model_id;
        $user_id = $validated_token->user_id;
        $access_token_id = $validated_token->id;
        $predictive_model = PredictiveModel::find($predictive_model_id);

        if (!$predictive_model->isActive()) {
            $this->api_service->logApiHit($source_ip, 'execute', '404', $user_id, $access_token_id);
            return response()->json(['error' => 'Model marked as Inactive'], 404);
        }

        $model_required_parameters = json_decode($predictive_model->required_parameters);
        $provided_parameters = [];

        foreach ($model_required_parameters as $model_required_parameter) {
            try {
                $provided_parameters[] = $request->input(str_replace(' ', '_', $model_required_parameter));
            } catch (\Exception $e) {
                $this->api_service->logApiHit($source_ip, 'execute', '500', $user_id, $access_token_id);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $response = $this->api_service->execute($predictive_model, $provided_parameters, $model_required_parameters);
        RateLimiter::hit($rate_limit_key, 60);

        if (!$response) {
            $this->api_service->logApiHit($source_ip, 'execute', '500', $user_id, $access_token_id);
            return response()->json(['error' => 'Error Processing Request'], 500);
        }

        try {
            $run_result = PredictiveModelRunResult::create([
                'model_id' => $predictive_model_id,
                'result' => json_encode(trim($response['Prediction'])),
                'inputs' => json_encode($response['Provided Parameters']),
                'actual'=> null,
            ]);
        } catch (\Exception $exception) {
            logger($exception);
        }

        $this->api_service->logApiHit($source_ip, 'execute', '200', $user_id, $access_token_id, $run_result->id ?? null);
        return response()->json($response, 200);
    }

    private function tokenResolver(Request $request): ?PredictiveModelAccessToken
    {
        $token_provided = $request->header('X-Access-Token');

        if (!$token_provided) {
            return null;
        }

        $validated_token = PredictiveModelAccessToken::query()->where('access_token', '=', hash('sha256', $token_provided))->first();

        if (!$validated_token) {
            return null;
        }

        return $validated_token;
    }
}
