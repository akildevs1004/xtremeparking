<?php

use App\Http\Controllers\FloorController;
use Illuminate\Support\Facades\Route;

Route::apiResource('floors', FloorController::class);
Route::get('floor-list', [FloorController::class, 'dropDown']);
