<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PredictiveModelApiController;

Route::prefix('v1')->group(function () {
    Route::get('describe_model', [PredictiveModelApiController::class, 'describeModel'])->name('api.describe');
    Route::post('execute_prediction', [PredictiveModelApiController::class, 'executePrediction'])->name('api.execute');
});
