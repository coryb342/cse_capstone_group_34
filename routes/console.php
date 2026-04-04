<?php

use App\Models\SensorViewingSession;
use App\Models\SoftSensor;
use App\Services\DockerExecutionService;
use App\Services\PredictiveModelAnalyticsService;
use App\Services\SoftSensorService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    $soft_sensor_service = new SoftSensorService(new DockerExecutionService(), new PredictiveModelAnalyticsService());
    $soft_sensors = SoftSensor::all();
    foreach ($soft_sensors as $soft_sensor) {
        if (SensorViewingSession::query()->where('organization_id', $soft_sensor->organization_id)->where('updated_at', '>', now()->subSeconds(10))->exists()){

            if ($soft_sensor->last_prediction_time < now()->subSeconds($soft_sensor->time_interval)){
                $lock_key = 'soft_sensor_lock_' . $soft_sensor->id;

                cache::lock($lock_key, 8)->get(function () use ($soft_sensor, $soft_sensor_service) {
                    $soft_sensor_service->updatePredictedValue($soft_sensor);
                });

            }
        }
    }
})->everySecond();
