<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoftSensor extends Model
{
    protected $fillable = [
        'mqtt_broker',
        'mqtt_topic',
        'model_id',
        'time_interval',
        'username',
        'password',
        'organization_id',
    ];
}
