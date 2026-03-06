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
            'sensors' => SoftSensor::all(),
            'models' => PredictiveModel::all(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mqtt_broker' => ['required', 'string'],
            'mqtt_topic' => ['required', 'string'],
            'model_id' => ['required', 'integer'],
            'time_interval' => ['required', 'integer', 'min:60'],
        ]);

        SoftSensor::create([
            'mqtt_broker' => $validated['mqtt_broker'],
            'mqtt_topic' => $validated['mqtt_topic'],
            'model_id' => $validated['model_id'],
            'time_interval' => $validated['time_interval'],


            'username' => auth()->user()->name,
            'password' => auth()->user()->password,
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
