<?php

use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/manage-users', [UserManagementController::class, 'index'])->middleware(['auth', 'verified'])->name('user-management');
Route::post('/manage-users/toggle-admin', [UserManagementController::class, 'toggleAdmin'])->middleware(['auth', 'verified'])->name('toggle-admin');
Route::post('/manage-users/toggle-status', [UserManagementController::class, 'toggleStatus'])->middleware(['auth', 'verified'])->name('toggle-status');
Route::post('/manage-users/delete-user', [UserManagementController::class, 'deleteUser'])->middleware(['auth', 'verified'])->name('delete-user');
Route::post('/manage-users/generate-access-code', [UserManagementController::class, 'generateAccessCode'])->middleware(['auth', 'verified'])->name('generate-access-code');



require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
