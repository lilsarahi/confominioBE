<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => response()->json(['status' => 'CondominioB API funcionando']));

Route::post('/mensaje', [ChatController::class, 'enviar']);
