<?php

use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

Route::get('/reservas',         [ReservaController::class, 'index']);
Route::post('/reservas',        [ReservaController::class, 'store']);
Route::put('/reservas/{id}',    [ReservaController::class, 'update']);
Route::delete('/reservas/{id}', [ReservaController::class, 'destroy']);