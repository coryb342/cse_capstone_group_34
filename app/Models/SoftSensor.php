<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SoftSensor extends Model
{
    protected $fillable = [
        "name",
        'mqtt_broker',
        'mqtt_topic',
        'model_id',
        'time_interval',
        'username',
        'password',
        'organization_id',
        'last_prediction_time'
    ];

    public function runResults(): belongsToMany
    {
        return $this->belongsToMany(PredictiveModelRunResult::class, 'soft_sensor_run_results');
    }
}
