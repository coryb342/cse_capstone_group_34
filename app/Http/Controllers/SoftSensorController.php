<?php

namespace App\Http\Controllers;

use App\Models\SensorViewingSession;
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
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }
        $organizationId = auth()->user()->organization_id;

        $sensors = SoftSensor::where('organization_id', $organizationId)->with(['runResults' => fn($q) => $q->latest()])->get();

        $models = PredictiveModel::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->with('analytics')
            ->get();

        $activeSensors = SoftSensor::where('organization_id', $organizationId)
            ->whereNotNull('updated_at')
            ->where('last_prediction_time', '>=', now()->subMinutes(5))
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
            'websocket_address' => ['required', 'string', 'max:255'],
            'mqtt_broker' => ['required', 'string'],
            'mqtt_topic' => ['required', 'string'],
            'username' => ['nullable', 'string'],
            'password' => ['nullable', 'string'],
            'model_id' => ['required', 'integer'],
            'time_interval' => ['required', 'integer', 'min:10'],
        ]);

        SoftSensor::create([
            'name' => $request->name,
            'websocket_address' => $request->websocket_address,
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

    public function initiateViewingSession(Request $request)
    {
        $user = auth()->user();
        $organization_id = $user->organization_id;

        $viewing_session = SensorViewingSession::query()->where('user_id', $user->id)->first();

        if ($viewing_session) {
            $viewing_session->touch();

            return redirect()->back();
        }

        SensorViewingSession::create([
            'organization_id' => $organization_id,
            'user_id' => $user->id,
        ]);

        return redirect()->back();
    }

    public function viewingSessionHeartbeat(Request $request)
    {
        $user = auth()->user();
        $organization_id = $user->organization_id;

        $viewing_session = SensorViewingSession::query()->where('user_id', $user->id)->first();

        if ($viewing_session) {
            $viewing_session = SensorViewingSession::create([
                'organization_id' => $organization_id,
                'user_id' => $user->id,
            ]);
        }

        $viewing_session->touch();

        return redirect()->back();
    }

    public function terminateViewingSession(Request $request)
    {
        $user = auth()->user();
        $active_session = SensorViewingSession::query()->where('user_id', $user->id)->first();

        if ($active_session) {
            $active_session->delete();
        }

        return redirect()->back();
    }
    public function destroy(SoftSensor $sensor)
    {
        $sensor->delete();
        return back();
    }
}
