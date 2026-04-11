<?php

namespace App\Http\Controllers;

use App\Models\PredictiveModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $organizationId = $user->organization_id;

        $extractNumber = function ($jsonOrScalar) {
            if ($jsonOrScalar === null) {
                return null;
            }

            if (is_string($jsonOrScalar) && is_numeric($jsonOrScalar)) {
                return (float) $jsonOrScalar;
            }

            if (is_numeric($jsonOrScalar)) {
                return (float) $jsonOrScalar;
            }

            if (is_string($jsonOrScalar)) {
                $decoded = json_decode($jsonOrScalar, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return null;
                }

                $v = $decoded;

                if (is_numeric($v)) {
                    return (float) $v;
                }

                if (is_string($v) && is_numeric($v)) {
                    return (float) $v;
                }

                if (is_array($v)) {
                    foreach (['value', 'result', 'prediction', 'y', 'pred'] as $key) {
                        if (array_key_exists($key, $v) && is_numeric($v[$key])) {
                            return (float) $v[$key];
                        }
                    }

                    if (count($v) === 1) {
                        $first = array_values($v)[0];

                        if (is_numeric($first)) {
                            return (float) $first;
                        }

                        if (is_string($first) && is_numeric($first)) {
                            return (float) $first;
                        }
                    }
                }
            }

            return null;
        };

        $start = Carbon::today()->subDays(30)->startOfDay();

        $models = PredictiveModel::where('organization_id', $organizationId)
            ->where('status', 'active')
            ->with(['analytics', 'runResults' => function ($query) use ($start) {
                $query->where('created_at', '>=', $start)
                    ->orderBy('created_at')
                    ->select('id', 'model_id', 'created_at', 'result', 'actual');
            }])
            ->get()
            ->map(function ($model) use ($extractNumber) {
                $points = [];

                foreach ($model->runResults as $run) {
                    $pred = $extractNumber($run->result);
                    $act = $extractNumber($run->actual);

                    if ($pred === null || $act === null) {
                        continue;
                    }

                    $points[] = [
                        'x' => $pred,
                        'y' => $act - $pred,
                        'run_id' => $run->id,
                        'created_at' => $run->created_at->toDateTimeString(),
                    ];
                }

                return [
                    'id' => $model->id,
                    'name' => $model->name,
                    'residualScatter' => [
                        'points' => $points,
                    ],
                    'analytics' => [
                        'mse' => $model->analytics?->mse,
                        'mae' => $model->analytics?->mae,
                        'rmse' => $model->analytics?->rmse,
                        'r2' => $model->analytics?->r2,
                        'accuracy' => $model->analytics?->accuracy,
                    ],
                ];
            })
            ->values();

        $zip = $request->input('zip') ?: '89502';
        $gauge = $request->input('gauge');

        // Weather stuff
        $location = Http::get("https://api.zippopotam.us/us/{$zip}")->json();

        $validZip = $location
            && isset($location['places'])
            && isset($location['places'][0]);

        if ($validZip) {
            $city = $location['places'][0]['place name'] ?? null;
            $state = $location['places'][0]['state abbreviation'] ?? null;

            $lat = isset($location['places'][0]['latitude'])
                ? (float) $location['places'][0]['latitude']
                : null;

            $lon = isset($location['places'][0]['longitude'])
                ? (float) $location['places'][0]['longitude']
                : null;

            if ($lat && $lon) {
                $weather = Http::get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat,
                    'longitude' => $lon,
                    'current' => ['temperature_2m', 'precipitation'],
                ])->json()['current'] ?? null;
            } else {
                $weather = null;
            }
        } else {
            $city = null;
            $state = null;
            $weather = null;
        }

        // River data
        $siteNumber = $gauge ?: '10348000';

        $usgs = Http::get('https://waterservices.usgs.gov/nwis/iv/', [
            'format' => 'json',
            'sites' => $siteNumber,
            'parameterCd' => '00065,00060',
        ])->json();

        $timeSeries = $usgs['value']['timeSeries'] ?? [];

        $siteName = $timeSeries[0]['sourceInfo']['siteName'] ?? 'Unknown gauge';
        $gageHeight = $timeSeries[0]['values'][0]['value'][0]['value'] ?? null;
        $discharge = $timeSeries[1]['values'][0]['value'][0]['value'] ?? null;

        return Inertia::render('Dashboard', [
            'models' => $models,
            'weather' => $weather,
            'zip' => $zip,
            'gauge' => $siteNumber,
            'gageHeight' => $gageHeight,
            'discharge' => $discharge,
            'riverName' => $siteName,
            'city' => $city,
            'state' => $state,
        ]);
    }
}
