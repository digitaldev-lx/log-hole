<?php

use DigitalDevLx\LogHole\Http\Controllers\LogHoleController;
use DigitalDevLx\LogHole\Middlewares\LogHoleDashboardAccessMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get(config('log-hole.dashboard_route', 'log-hole'), [LogHoleController::class, 'index'])
        ->middleware(LogHoleDashboardAccessMiddleware::class)
        ->name('log-hole.dashboard');
});
