<?php

namespace App\Http\Controllers;

use App\Models\SoftSensor;
use App\Models\PredictiveModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SoftSensorController extends Controller
{
    public function index()
    {
        $organizationId = auth()->user()->organization_id;

        $sensors = SoftSensor::where('organization_id', $organizationId)->get();
        $models = PredictiveModel::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->with('analytics')
            ->get();

        $activeSensors = SoftSensor::where('organization_id', $organizationId)
            ->whereNotNull('updated_at')
            ->where('updated_at', '>=', now()->subMinutes(5))
            ->count();
        $totalSensors = $sensors->count();

        $avgAccuracy = round(
            $models
                ->filter(fn($m) => $m->analytics?->accuracy !== null && $m->analytics->accuracy != 0)
                ->avg(fn($m) => $m->analytics->accuracy) ?? 0,
            2
        );

        $modelsOnline = $models->count();

        return Inertia::render('SoftSensors', [
            'sensors' => $sensors,
            'models' => $models,
            'stats' => [
                'activeSensors' => $activeSensors,
                'totalSensors' => $totalSensors,
                'avgAccuracy' => $avgAccuracy,
                'modelsOnline' => $modelsOnline,
            ],
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
            'password' => $request->password ? Crypt::encryptString($request->password) : null,
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
