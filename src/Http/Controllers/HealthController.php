<?php

namespace EISALL\TaskHubConnector\Http\Controllers;

use Illuminate\Support\Facades\App;

class HealthController
{
    public function __invoke()
    {
        return response()->json([
            'status' => 'ok',
            'app' => config('taskhub.slug'),
            'framework' => 'laravel/'.App::version(),
            'php' => PHP_VERSION,
            'time' => now()->toIso8601String(),
            'connector' => '1.0.0',
        ]);
    }
}
