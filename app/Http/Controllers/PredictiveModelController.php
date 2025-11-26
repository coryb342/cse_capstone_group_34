<?php

namespace App\Http\Controllers;

use App\Models\PredictiveModel;
use App\Models\PredictiveModelRunResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PredictiveModelController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $organization_id = $user->organization_id;
        $predictive_models = PredictiveModel::query()->where('organization_id', '=', $organization_id)->get();

        $modelsData = [];

        foreach ($predictive_models as $pm){
            $runs = $pm->runResults->filter(function($result){
                return !is_null($result->actual);
            });

            $accuracy = null;

            if ($runs->count() > 0) {
                // arrays for calculateMetrics()
                $preds = $runs->map(function($result){
                    return $result->result['predicted'];
                })->toArray();
                $acts = $runs->map(function($result){
                    return $result->actual['value'];
                })->toArray();
                $metrics = $this->calculateMetrics($preds, $acts);
                // return accuracy
                $accuracy = $metrics['Accuracy'] ?? null;
            }

            $modelsData[] = [
                'model' => $pm,
                'accuracy' => $accuracy,
                'totalModelPredictions' => $pm->runResults->count(),
            ];
        }
        $totalPredictions = 0;
        $n = count($predictive_models);
        for($i =0; $i < $n; $i++){
            $totalPredictions += PredictiveModelRunResult::query()->where('model_id', '=', $predictive_models[$i]->id)->count();
        }

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
        $model = PredictiveModel::with('runResults')->findOrFail($id);
        $modelCreatedDate = Carbon::parse($model->created_at)->format('m-d-Y');
        $modelLastTrainedDate = Carbon::parse($model->last_trained_on)->format('m-d-Y');
        $runResults = $model->runResults;
        $totalPredictions = $model->runResults->count();

        $resultsWithActuals = $runResults->filter(function($result){
            return !is_null($result->actual);
        });
        $resultsWithoutActuals = $runResults->filter(function($result){
            return is_null($result->actual);
        });

        $aggregateMetrics = null;
        if($resultsWithActuals->count() > 0){
            $predictions = $resultsWithActuals->map(function($result){
                return $result->result['predicted'];
            })->toArray();
            $actuals = $resultsWithActuals->map(function($result){
                return $result->actual['value'];
            })->toArray();

            $aggregateMetrics = $this->calculateMetrics($predictions, $actuals);
        }

        return Inertia::render('PredictiveModelShow', ['model' => $model, 'run_results' => $model->runResults, 'totalPredictions' => $totalPredictions, 'aggregateMetrics' => $aggregateMetrics, 'modelCreatedDate' => $modelCreatedDate, 'modelLastTrainedDate' => $modelLastTrainedDate], );
    }
    private function calculateMetrics(array $predictions, array $actuals): array
    {
        $n = count($predictions);
        $predictions = array_values($predictions);
        $actuals = array_values($actuals);

        //Mean Absolute Percentage Error
        $mape = 0;
        for ($i = 0; $i < $n; $i++) {
            if ($actuals[$i] != 0) {
                $mape += abs(($actuals[$i] - $predictions[$i]) / $actuals[$i]);
            }
        }
        $mape = ($mape / $n) * 100;
        $accuracy = 100 - $mape;

        // Mean Absolute Error - avg( |actual - pred| )
        $mae = 0;
        for ($i = 0; $i < $n; $i++){
            $mae += abs($actuals[$i]-$predictions[$i]);
        }
        $mae = $mae / $n;

        //Mean Squared Error
        $mse = 0;
        for ($i = 0; $i < $n; $i++){
            $error = $actuals[$i] - $predictions[$i];
            $mse += $error*$error;
        }
        $mse = $mse / $n;

        // Root Mean Squared Error
        $rmse = sqrt($mse);

        //R^2 coefficient of determination (1 - (SSR / SST))
        $sumActual = 0;
        for($i = 0; $i < $n; $i++){
            $sumActual += $actuals[$i];
        }
        $meanActual = $sumActual/$n;
        $ssR = 0; // sum of squared residuals
        $ssT = 0; // total sum of squares

        for($i = 0; $i  < $n; $i++){
            $ssR += pow($actuals[$i] - $predictions[$i], 2);
            $ssT += pow($actuals[$i] - $meanActual, 2);
        }
        $r2 = $ssT > 0 ? 1 - ($ssR / $ssT) : 0;

        return ['Accuracy' => round($accuracy, 2),
            'MAE' => round($mae, 4),
            'MSE' => round($mse, 4),
            'RMSE' => round($rmse, 4),
            'R2' => round($r2, 4)
        ];
    }
}
