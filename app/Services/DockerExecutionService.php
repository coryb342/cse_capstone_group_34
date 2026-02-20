<?php
namespace App\Services;

use App\Models\PredictiveModel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DockerExecutionService
{
    public function runPrediction(PredictiveModel $model, array $parameters)
    {
        $temp_dir = sys_get_temp_dir() . '/model_tmp';
        if (!File::exists($temp_dir)) {
            File::makeDirectory($temp_dir, 0755, true);
        }

        $model_path = Storage::disk('private')->path($model->getPath());
        $model_filename = basename($model_path);
        $temp_model_path = $temp_dir . '/' . $model_filename;

        File::copy($model_path, $temp_model_path);

        $cmd = "docker run --rm "
            . "-v {$temp_dir}:/models "
            . "-e MODEL_PATH=/models/{$model_filename} "
            . "run_prediction_image python run_prediction.py "
            . implode(' ', $parameters);
        $prediction = shell_exec($cmd);

        return $prediction;
    }
}
