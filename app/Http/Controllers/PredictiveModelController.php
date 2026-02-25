<?php

namespace App\Http\Controllers;

use App\Models\PredictiveModel;
use App\Models\PredictiveModelRunResult;
use App\Services\DockerExecutionService;
use App\Services\PredictiveModelAnalyticsService;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PredictiveModelController extends Controller
{

    public function __construct(
        protected DockerExecutionService $execution_service,
    )
    {
    }
    public function index()
    {
        $user = Auth::user();
        $organization_id = $user->organization_id;
        $predictive_models = PredictiveModel::query()->where('organization_id', '=', $organization_id)->with('analytics', 'latestRunResult')->get();

        $modelsData = [];

        foreach ($predictive_models as $pm){
            $modelsData[] = [
                'model' => $pm,
                'accuracy' => $pm->analytics?->accuracy,
                'totalModelPredictions' => $pm->analytics?->total_predictions ?? 0,
            ];
        }

        $totalPredictions = \App\Models\PredictiveModelAnalytics::query()
            ->whereIn('model_id', $predictive_models->pluck('id'))
            ->sum('total_predictions');

        return Inertia::render('PredictiveModels', ['models' => $predictive_models, 'total_predictions' => $totalPredictions, 'modelData' => $modelsData]);
    }

    public function upload(Request $request) {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            return redirect()->back()->withErrors(['Unauthorized action.']);
        }

        $request->validate([
            'model_name' => 'required',
            'model_description' => 'required',
            'model_type' => 'required',
            'required_parameters' => 'required',
            'target' => 'required',
            'model_file' => 'required|file|max:256000',
            'model_accuracy' => 'nullable|max:100|min:0',
            'last_trained_on' => 'nullable|date',
        ]);

        $required_parameters = preg_split('/\s*,\s*/', $request->input('required_parameters'), -1, PREG_SPLIT_NO_EMPTY);
        $model_file = $request->file('model_file');

        $predictive_model = PredictiveModel::create([
            'organization_id' => $user->organization_id,
            'name' => $request->input('model_name'),
            'description' => $request->input('model_description'),
            'type' => $request->input('model_type'),
            'required_parameters' => json_encode($required_parameters),
            'target' => $request->input('target'),
            'accuracy' => $request->input('model_accuracy') ? $request->input('model_accuracy') : null,
            'last_trained_on' => $request->input('last_trained_on') ? $request->input('last_trained_on') : now(),
        ]);

        $predictive_model->save();
        $predictive_model_id = $predictive_model->id;

        $directory_path = $user->organization_id . '/models/' . $predictive_model_id;
        if (!Storage::disk('private')->exists($directory_path)) {
            Storage::disk('private')->makeDirectory($directory_path);
        }

        $model_file->storeAs($directory_path, $model_file->getClientOriginalName(), 'private');

        $predictive_model->update(['path' => $directory_path . '/' . $model_file->getClientOriginalName()]);

        return redirect()->back()->with(['success' => $predictive_model->name . ' uploaded successfully.']);
    }
    public function show($id)
    {
        $model = PredictiveModel::with('runResults', 'analytics')->findOrFail($id);
        $modelCreatedDate = Carbon::parse($model->created_at)->format('m-d-Y');
        $modelLastTrainedDate = Carbon::parse($model->last_trained_on)->format('m-d-Y');
        $runResults = $model->runResults;
        $totalPredictions = $model->runResults->count();
        $analytics = $model->analytics;

        $start = Carbon::today()->subDays(30)->startOfDay();

        $runs = $model->runResults()
            ->where('created_at', '>=', $start)
            ->orderBy('created_at')
            ->get(['id', 'created_at', 'result', 'actual']);

//
        $extractNumber = function ($jsonOrScalar) {
            if ($jsonOrScalar === null) return null;

            // If it's already a numeric scalar string like "12.3"
            if (is_string($jsonOrScalar) && is_numeric($jsonOrScalar)) {
                return (float) $jsonOrScalar;
            }

            // If it's JSON stored as string (your case)
            if (is_string($jsonOrScalar)) {
                $decoded = json_decode($jsonOrScalar, true);

                // If decoding fails, nothing we can do
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return null;
                }

                // decoded might be: 12.3, "12.3", [12.3], {"value":12.3}, {"result":12.3}
                $v = $decoded;

                if (is_numeric($v)) return (float) $v;

                if (is_string($v) && is_numeric($v)) return (float) $v;

                if (is_array($v)) {
                    // common object keys
                    foreach (['value', 'result', 'prediction', 'y', 'pred'] as $key) {
                        if (array_key_exists($key, $v) && is_numeric($v[$key])) {
                            return (float) $v[$key];
                        }
                    }

                    // array like [12.3]
                    if (count($v) === 1) {
                        $first = array_values($v)[0];
                        if (is_numeric($first)) return (float) $first;
                        if (is_string($first) && is_numeric($first)) return (float) $first;
                    }
                }
            }

            return null;
        };

        $points = [];

        foreach ($runs as $r) {
            $pred = $extractNumber($r->result);
            $act  = $extractNumber($r->actual);

            if ($pred === null || $act === null) continue;

            $points[] = [
                'x' => $pred,
                'y' => $act - $pred,
                'run_id' => $r->id,
                'created_at' => $r->created_at->toDateTimeString(),
            ];
        }
        return Inertia::render('PredictiveModelShow', ['model' => $model, 'run_results' => $model->runResults, 'totalPredictions' => $totalPredictions, 'analytics' => $analytics, 'modelCreatedDate' => $modelCreatedDate, 'modelLastTrainedDate' => $modelLastTrainedDate, 'residualScatter' => [
            'points' => $points]]);
    }
    public function run(Request $request) {
        $request->validate([
            'model_id' => 'required',
            'parameters' => 'required',
        ]);

        $user = Auth::user();
        $model = PredictiveModel::query()->where('id', '=', $request->get('model_id'))->first();
        $parameters = $request->get('parameters');
        $required_parameters = json_decode($model->required_parameters);
        $required_parameter_count = count($required_parameters);

        if (count($parameters) != $required_parameter_count) {
            return redirect()->back()->withErrors(['parameters' => 'Incorrect number of parameters']);
        }

        $mapped_parameters = array_combine($required_parameters, $parameters);

        $prediction = $this->execution_service->runPrediction($model, $parameters);

        if (!$prediction) {
            return redirect()->back()->withErrors(['prediction_failed' => 'Prediction failed']);
        }

        if(Str::contains($prediction, 'Error:')) {
            return redirect()->back()->with(['prediction_failed' =>$prediction, 'mapped_parameters' => $mapped_parameters]);
        }

        $result = trim($prediction);

        $run_result = PredictiveModelRunResult::create([
            'model_id' => $request->get('model_id'),
            'result' => json_encode($result),
            'inputs' => json_encode($mapped_parameters),
            'actual' => $request->get('actual') ?? null,
        ]);

        app(PredictiveModelAnalyticsService::class)->recomputeForModel($model->id);
        $run_result->save();

        return redirect()->back()->with(['model_run_result' => $result, 'mapped_parameters' => $mapped_parameters]);
    }
}
