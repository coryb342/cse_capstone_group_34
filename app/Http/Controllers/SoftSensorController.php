<?php

namespace App\Http\Controllers;

use App\Models\SoftSensor;
use App\Models\PredictiveModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SoftSensorController extends Controller
{
    public function index()
    {
        return Inertia::render('SoftSensors', [
            'sensors' => SoftSensor::all()->map(function ($sensor) {
                return [
                    ...$sensor->toArray(),
                    'time_since_last_prediction' => $sensor->last_prediction_at
                        ? $sensor->last_prediction_at->diffForHumans()
                        : 'No Predictions Yet',
                ];
            }),
            'models' => PredictiveModel::all(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mqtt_broker' => ['required', 'string'],
            'mqtt_topic' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
            'time_interval' => ['required', 'integer', 'min:60'],
        ]);

        SoftSensor::create([
            'name' => $request->name,
            'mqtt_broker' => $request->mqtt_broker,
            'mqtt_topic' => $request->mqtt_topic,
            'username' => $request->username,
            'password' => $request->password,
            'model_id' => $request->model_id,
            'time_interval' => $request->time_interval,
            'organization_id' => auth()->user()->organization_id,
        ]);

        return redirect()->back();
    }
    public function destroy(SoftSensor $sensor)
    {
        $sensor->delete();
        return back();
    }
}
