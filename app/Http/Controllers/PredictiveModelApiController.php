<?php

namespace App\Http\Controllers;

use App\Models\PredictiveModel;
use App\Models\PredictiveModelAccessToken;
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
        $rate_limit_key = self::API_RATE_LIMIT_KEY_PREFIX . $request->ip();

        if (RateLimiter::tooManyAttempts($rate_limit_key, $perMinute = self::API_RATE_LIMIT_PER_MIN)) {
            return response()->json(['error' => 'Too many attempts.'], 429);
        }

        $validated_token = $this->tokenResolver($request);

        if (!$validated_token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $predictive_model_id = $validated_token->model_id;
        $response = $this->api_service->describe($predictive_model_id);
        RateLimiter::hit($rate_limit_key, 60);

        if (!$response) {
            return response()->json(['error' => 'Not Found'], 404);
        }

        return response()->json($response, 200);
    }

    public function executePrediction(Request $request): JsonResponse
    {
        $rate_limit_key = self::API_RATE_LIMIT_KEY_PREFIX . $request->ip();

        if (RateLimiter::tooManyAttempts($rate_limit_key, $perMinute = self::API_RATE_LIMIT_PER_MIN)) {
            return response()->json(['error' => 'Too many attempts.'], 429);
        }

        $validated_token = $this->tokenResolver($request);

        if (!$validated_token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $predictive_model_id = $validated_token->model_id;
        $predictive_model = PredictiveModel::find($predictive_model_id);

        if (!$predictive_model->isActive()) {
            return response()->json(['error' => 'Model marked as Inactive'], 404);
        }

        $model_required_parameters = json_decode($predictive_model->required_parameters);
        $provided_parameters = [];

        foreach ($model_required_parameters as $model_required_parameter) {
            try {
                $provided_parameters[] = $request->input(str_replace(' ', '_', $model_required_parameter));
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $response = $this->api_service->execute($predictive_model, $provided_parameters, $model_required_parameters);
        RateLimiter::hit($rate_limit_key, 60);

        if (!$response) {
            return response()->json(['error' => 'Error Processing Request'], 500);
        }

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
