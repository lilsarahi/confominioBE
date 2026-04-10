<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole {
    public function handle(Request $request, Closure $next, string ...$roles) {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        //Si es admin, pasa siempre
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verificar si tiene alguno de los roles requeridos
        foreach ($roles as $rol) {
            if ($user->hasRole($rol)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'No tienes permiso para esta acción'], 403);
    }
}