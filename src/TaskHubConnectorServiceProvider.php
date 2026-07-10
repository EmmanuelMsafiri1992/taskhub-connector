<?php

namespace EISALL\TaskHubConnector;

use EISALL\TaskHubConnector\Http\Middleware\VerifyTaskHubSignature;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TaskHubConnectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/taskhub.php', 'taskhub');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/taskhub.php' => config_path('taskhub.php'),
        ], 'taskhub-config');

        // Inert until the app is registered in TaskHub and given a secret.
        if (! config('taskhub.secret')) {
            return;
        }

        Route::prefix('_taskhub')
            ->middleware(VerifyTaskHubSignature::class)
            ->group(__DIR__.'/../routes/taskhub.php');
    }
}
