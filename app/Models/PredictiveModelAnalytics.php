<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PredictiveModelAnalytics extends Model
{
    protected $table = 'predictive_model_analytics';

    protected $fillable = [
        'model_id',
        'organization_id',
        'total_predictions',
        'total_failed_predictions',
        'evaluated_predictions',
        'mse',
        'mae',
        'rmse',
        'r2',
        'mape',
        'accuracy',
    ];

    public function model()
    {
        return $this->belongsTo(PredictiveModel::class, 'model_id');
    }
}
