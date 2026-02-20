<?php
namespace App\Services;

use App\Models\PredictiveModel;
use App\Models\PredictiveModelAnalytics;
use App\Models\PredictiveModelRunResult;
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

        try {
            PredictiveModelRunResult::create([
                'model_id' => $model->id,
                'result' => json_encode(trim($prediction)),
                'inputs' => json_encode($mapped_parameters),
                'actual'=> null,
            ]);
        } catch (\Exception $exception) {
            return null;
        }

        return $result;
    }
}
