<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReservaController;
use Illuminate\Support\Facades\Route;

Route::post('/register',                        [AuthController::class, 'register']);
Route::post('/login',                           [AuthController::class, 'login']);
Route::post('/email/resend',                    [AuthController::class, 'resendVerification']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify')
    ->middleware(['signed']);
    
Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    Route::get('/user',                         [AuthController::class, 'me']);
    Route::post('/logout',                      [AuthController::class, 'logout']);
    Route::post('/cambiar-password',            [AuthController::class, 'cambiarPassword']);

    Route::get('/reservas',                     [ReservaController::class, 'index']);
    Route::post('/reservas',                    [ReservaController::class, 'store']);
    Route::put('/reservas/{id}',                [ReservaController::class, 'update']);
    Route::delete('/reservas/{id}',             [ReservaController::class, 'destroy']);

});