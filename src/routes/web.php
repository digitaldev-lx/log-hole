<?php

use DigitalDevLx\LogHole\Http\Controllers\LogHoleController;

Route::get(config('log-hole.dashboard_route'), [LogHoleController::class, 'index'])
    ->name('log-hole.dashboard')
    ->middleware('can:view-log-dashboard');
