<?php

namespace App\Http\Controllers;

use App\Models\PredictiveModelAccessToken;
use App\Services\PredictiveModelApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PredictiveModelApiController extends Controller
{
    public function __construct(
        protected PredictiveModelApiService $api_service
    )
    {
    }

    public function describe(Request $request): JsonResponse
    {

        $validated_token = $this->tokenResolver($request);

        if (!$validated_token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $predictive_model_id = $validated_token->model_id;

        $response = $this->api_service->describe($predictive_model_id);

        if (!$response) {
            return response()->json(['error' => 'Not Found'], 404);
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
