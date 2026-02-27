<?php
namespace App\Services;

use App\Models\ApiLog;
use App\Models\PredictiveModel;
use App\Models\PredictiveModelAnalytics;
use Illuminate\Support\Str;

class PredictiveModelApiService
{
    public function __construct(
        protected DockerExecutionService $execution_service,
    )
    {
    }

    public function describe(int $predictive_model_id): ?array
    {
        $predictive_model = PredictiveModel::find($predictive_model_id);

        if (!$predictive_model) {
            return null;
        }

        $predictive_model_analytics = PredictiveModelAnalytics::query()->where('model_id', $predictive_model_id)->first();

        $description = [
            'Model Name' => $predictive_model->name,
            'Model Type' => $predictive_model->type,
            'Status' => $predictive_model->status,
            'Model Description' => $predictive_model->description ?? 'N/A',
            'Accuracy' => $predictive_model_analytics->accuracy ?? 'N/A',
            'Required Headers' => 'X-Access-Token',
            'Required Parameters' => json_decode($predictive_model->required_parameters),
            'Target' => $predictive_model->target,
        ];
        return $description;
    }

    public function execute(PredictiveModel $model, array $provided_parameters, array $required_parameters): ?array
    {
        $mapped_parameters = array_combine($required_parameters, $provided_parameters);

        try {
            $prediction = $this->execution_service->runPrediction($model, $provided_parameters);
        } catch (\Exception $exception) {
            return null;
        }

        if (Str::contains($prediction, 'Error:')) {
            return null;
        }

        $result = [
            'Provided Parameters' => $mapped_parameters,
            'Prediction' => trim($prediction),
        ];

        return $result;
    }

    public function logApiHit(string $source_ip, string $method, int $response_code, int $user_id = null, int $predictive_model_access_token_id = null, int $predictive_model_run_result_id = null): void
    {
        ApiLog::create(
            [
                'source_ip' => $source_ip,
                'user_id' => $user_id,
                'predictive_model_access_token_id' => $predictive_model_access_token_id,
                'method' => $method,
                'predictive_model_run_result_id' => $predictive_model_run_result_id,
                'response_code' => $response_code,
            ]
        );
    }
}
