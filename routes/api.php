<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReservaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/email/resend', [AuthController::class, 'resendVerification']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
     ->name('verification.verify');

//Rutas protegidas por Sanctum 
Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    Route::get('/reservas',         [ReservaController::class, 'index']);
    Route::post('/reservas',        [ReservaController::class, 'store']);
    Route::put('/reservas/{id}',    [ReservaController::class, 'update']);
    Route::delete('/reservas/{id}', [ReservaController::class, 'destroy']);

    Route::get('/user', function (Request $request) {
        $user = $request->user()->load('persona');
        return response()->json([
            'id'             => $user->id,
            'admin'          => $user->admin,
            'roles'          => $user->getRoles(),
            'nombre_completo'=> $user->persona->nombre_completo ?? '',
            'email'          => $user->persona->email ?? '',
        ]);
    });
});

