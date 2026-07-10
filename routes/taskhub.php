<?php

use EISALL\TaskHubConnector\Http\Controllers\HealthController;
use EISALL\TaskHubConnector\Http\Controllers\ResourceController;
use EISALL\TaskHubConnector\Http\Controllers\SchemaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TaskHub connector routes
|--------------------------------------------------------------------------
|
| Stateless and signature-authenticated: deliberately outside the web and
| api middleware groups, so no session, no cookies and no CSRF are in play.
| Only registered at all when TASKHUB_SECRET is configured.
|
*/

Route::get('health', HealthController::class);
Route::get('schema', SchemaController::class);

Route::get('resources/{resource}', [ResourceController::class, 'index']);
Route::post('resources/{resource}', [ResourceController::class, 'store']);
Route::get('resources/{resource}/{id}', [ResourceController::class, 'show']);
Route::patch('resources/{resource}/{id}', [ResourceController::class, 'update']);
Route::delete('resources/{resource}/{id}', [ResourceController::class, 'destroy']);
