<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::post('/mensaje', [ChatController::class, 'enviar']);
