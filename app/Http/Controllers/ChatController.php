<?php

namespace App\Http\Controllers;

use App\Events\MensajeEnviado;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function enviar(Request $request)
    {
        $request->validate([
            'mensaje'   => 'required|string|max:500',
            'remitente' => 'nullable|string|max:100',
        ]);

        $mensaje   = $request->input('mensaje');
        $remitente = $request->input('remitente', 'Usuario');

        broadcast(new MensajeEnviado($mensaje, $remitente));

        return response()->json([
            'status'    => 'ok',
            'mensaje'   => $mensaje,
            'remitente' => $remitente,
        ]);
    }
}
