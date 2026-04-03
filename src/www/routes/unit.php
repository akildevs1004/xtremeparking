<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UnitController;

Route::apiResource('units', UnitController::class);
Route::get('unit-list', [UnitController::class, 'dropDown']);
Route::get('rooms-by-floors', [UnitController::class, 'roomsByFloors']);