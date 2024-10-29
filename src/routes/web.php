<?php

use DigitalDevLx\LogHole\Http\Controllers\LogHoleController;

Route::get(config('log-hole.dashboard_route'), [LogHoleController::class, 'index'])
    ->middleware(DigitalDevLx\LogHole\Middlewares\LogHoleDashboardAccessMiddleware::class)
    ->name('log-hole.dashboard');
