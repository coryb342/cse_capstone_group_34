<?php
namespace App\Services;

use App\Models\PredictiveModel;
use App\Models\PredictiveModelAnalytics;
use Illuminate\Support\Facades\DB;

class PredictiveModelAnalyticsService
{
    public function recomputeForModel(int $modelId): PredictiveModelAnalytics
    {
        return DB::transaction(function () use ($modelId) {
            $model = PredictiveModel::query()->with('runResults')->findOrFail($modelId);
            $runs = $model->runResults;
            $totalPredictions = $runs->count();

            // evaluated = has actual AND we can parse both predicted/actual into floats
            $evaluatedPairs = $runs->map(function ($run) {
                $pred = $run->predictedValue();
                $act = $run->actualValue();

                if ($pred === null || $act === null) {
                    return null;
                }

                return [
                    'pred' => (float)$pred,
                    'act' => (float)$act,
                ];
            })->filter()->values();

            $evaluatedCount = $evaluatedPairs->count();

            // failed = couldn't parse predicted at all
            $failedCount = $runs->filter(fn($run) => $run->predictedValue() === null)->count();

            $predictions = $evaluatedPairs->pluck('pred')->toArray();
            $actuals = $evaluatedPairs->pluck('act')->toArray();

            $metrics = $evaluatedCount > 0 ? $this->calculateMetrics($predictions, $actuals): [
                    'Accuracy' => null,
                    'MAE' => null,
                    'MSE' => null,
                    'RMSE' => null,
                    'R2' => null,
            ];

            $dbMetrics = [
                'mse' => $metrics['MSE'] ?? null,
                'mae' => $metrics['MAE'] ?? null,
                'rmse' => $metrics['RMSE'] ?? null,
                'r2' => $metrics['R2'] ?? null,
                'accuracy' => $metrics['Accuracy'] ?? null,
                'mape' => isset($metrics['Accuracy']) ? (100.0 - (float)$metrics['Accuracy']) : null,
            ];

            return PredictiveModelAnalytics::updateOrCreate(
                ['model_id' => $modelId],
                array_merge($dbMetrics, [
                    'organization_id' => $model->organization_id,
                    'total_predictions' => $totalPredictions,
                    'total_failed_predictions' => $failedCount,
                    'evaluated_predictions' => $evaluatedCount,
                ])
            );
        });
    }
    private function calculateMetrics(array $predictions, array $actuals): array
    {
        $n = count($predictions);
        $predictions = array_values($predictions);
        $actuals = array_values($actuals);

        //Mean Absolute Percentage Error
        $mape = 0;
        $mapeCount = 0;
        for ($i = 0; $i < $n; $i++) {
            if ($actuals[$i] != 0) {
                $mape += abs(((float)$actuals[$i] - (float)$predictions[$i]) / (float)$actuals[$i]);
                $mapeCount++;
            }
        }
        $mape = $mapeCount > 0 ? ($mape / $mapeCount) * 100 : 0;
        $accuracy = 100 - $mape;

        // Mean Absolute Error - avg( |actual - pred| )
        $mae = 0;
        for ($i = 0; $i < $n; $i++){
            $mae += abs((float)$actuals[$i]-(float)$predictions[$i]);
        }
        $mae = $mae / $n;

        //Mean Squared Error
        $mse = 0;
        for ($i = 0; $i < $n; $i++){
            $error = (float)$actuals[$i] - (float)$predictions[$i];
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
            $ssR += pow((float)$predictions[$i] - (float)$actuals[$i] , 2);
            $ssT += pow((float)$actuals[$i] - (float)$meanActual, 2);
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
