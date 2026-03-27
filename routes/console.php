<?php

use App\Models\SoftSensor;
use App\Services\DockerExecutionService;
use App\Services\PredictiveModelAnalyticsService;
use App\Services\SoftSensorService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    $soft_sensor_service = new SoftSensorService(new DockerExecutionService(), new PredictiveModelAnalyticsService());
    $soft_sensors = SoftSensor::all();
    foreach ($soft_sensors as $soft_sensor) {
        if ($soft_sensor->last_prediction_time < now()->subSeconds($soft_sensor->time_interval))
        $soft_sensor_service->updatePredictedValue($soft_sensor);
    }
})->everySecond();
