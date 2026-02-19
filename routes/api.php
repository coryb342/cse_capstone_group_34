<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PredictiveModelApiController;

Route::prefix('v1')->group(function () {
    Route::get('describe', [PredictiveModelApiController::class, 'describe'])->name('api.describe');
});
