<?php

use App\Http\Controllers\PredictiveModelAccessTokenController;
use App\Http\Controllers\PredictiveModelController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/about', function () {
    return view('about');
});

//Dashboard
Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');


//User Management Routes
Route::get('/manage-users', [UserManagementController::class, 'index'])->middleware(['auth', 'verified'])->name('user-management');
Route::post('/manage-users/toggle-admin', [UserManagementController::class, 'toggleAdmin'])->middleware(['auth', 'verified'])->name('toggle-admin');
Route::post('/manage-users/toggle-status', [UserManagementController::class, 'toggleStatus'])->middleware(['auth', 'verified'])->name('toggle-status');
Route::post('/manage-users/delete-user', [UserManagementController::class, 'deleteUser'])->middleware(['auth', 'verified'])->name('delete-user');
Route::post('/manage-users/generate-access-code', [UserManagementController::class, 'generateAccessCode'])->middleware(['auth', 'verified'])->name('generate-access-code');

//Predictive Model Routes
Route::get('/predictive-models', [PredictiveModelController::class, 'index'])->middleware(['auth', 'verified'])->name('predictive-models');
Route::post('/predictive-models/upload', [PredictiveModelController::class, 'upload'])->middleware(['auth', 'verified'])->name('predictive-models-upload');

// Access Token Route
Route::get('/access-tokens', [PredictiveModelAccessTokenController::class, 'index'])->middleware(['auth', 'verified'])->name('access-tokens-index');
Route::post('/access-tokens/grant-access', [PredictiveModelAccessTokenController::class, 'grantAccess'])->middleware(['auth', 'verified'])->name('access-token-grant-access');
Route::delete('/access-tokens/{accessToken}', [PredictiveModelAccessTokenController::class, 'destroy'])->middleware(['auth', 'verified'])->name('access-tokens.destroy');
Route::post('/access-tokens/{accessToken}/activate', [PredictiveModelAccessTokenController::class, 'activate'])->middleware(['auth', 'verified'])->name('access-tokens.activate');

// Predictive Model Show
Route::get('predictive-models/{id}', [PredictiveModelController::class, 'show'])->middleware(['auth', 'verified'])->name('predictive-models.show');
Route::post('predictive-models/run', [PredictiveModelController::class, 'run'])->middleware(['auth', 'verified'])->name('predictive-models.run');

// Soft Sensor
Route::get('/soft-sensors', function () {
    return Inertia::render('SoftSensors');
})->middleware(['auth', 'verified'])->name('soft-sensors');



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
