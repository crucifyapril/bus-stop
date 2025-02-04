<?php

use App\Http\Controllers\BusController;
use App\Http\Controllers\BusRouteController;
use Illuminate\Support\Facades\Route;

Route::apiResource('routes', BusRouteController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

Route::get('/find-bus', [BusController::class, 'findBus']);
