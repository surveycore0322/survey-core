<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\TelemetryController;

Route::prefix('v1')->group(function () {
    Route::post('/forms', [FormController::class, 'create']);
    Route::get('/forms/{public_token}', [FormController::class, 'show']);
    Route::post('/forms/{public_token}/snapshots', [FormController::class, 'submitSnapshot']);
    Route::get('/forms/{public_token}/results', [FormController::class, 'results']);

    Route::post('/telemetry', [TelemetryController::class, 'store']);
});