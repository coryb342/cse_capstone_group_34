<?php
namespace App\Services;

use App\Models\PredictiveModel;
use App\Models\PredictiveModelAnalytics;

class PredictiveModelApiService
{
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
}
