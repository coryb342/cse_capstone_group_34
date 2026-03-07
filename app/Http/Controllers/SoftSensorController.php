<?php

namespace App\Http\Controllers;

use App\Models\SoftSensor;
use App\Models\PredictiveModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;


class SoftSensorController extends Controller
{
    public function index()
    {
        $organizationId = auth()->user()->organization_id;

        return Inertia::render('SoftSensors', [
            'sensors' => SoftSensor::where('organization_id', $organizationId)->get(),
            'models' => PredictiveModel::where('organization_id', $organizationId)
                ->where('status', 'active')
                ->get(['id', 'name']),

        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mqtt_broker' => ['required', 'string'],
            'mqtt_topic' => ['required', 'string'],
            'username' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'model_id' => ['required', 'integer'],
            'time_interval' => ['required', 'integer', 'min:60'],
        ]);

        SoftSensor::create([
            'name' => $request->name,
            'mqtt_broker' => $request->mqtt_broker,
            'mqtt_topic' => $request->mqtt_topic,
            'username' => $request->username ?: null,
            'password' => $request->password ? Hash::make($request->password) : null,
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
