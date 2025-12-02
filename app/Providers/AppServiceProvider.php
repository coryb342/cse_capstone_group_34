<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'flash' => function () {
                return [
                    'success' => session('success'),
                    'code' => session('code'),
                    'model_run_result' => session('model_run_result'),
                    'mapped_parameters' => session('mapped_parameters'),
                    'prediction_failed' => session('prediction_failed'),
                    'token' => session('token'),
                ];
            },
        ]);
    }
}
