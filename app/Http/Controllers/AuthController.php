<?php
namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Usuario;
use App\Notifications\VerificarEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //Registrar
    public function register(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'apellido_p'  => 'required|string|max:100',
            'apellido_m'  => 'required|string|max:100',
            'celular'     => 'required|numeric',
            'email'       => 'required|email|unique:personas,email',
            'password'    => 'required|string|min:8|confirmed',
        ]);

        //Crear persona
        $persona = Persona::create([
            'nombre'     => $request->nombre,
            'apellido_p' => $request->apellido_p,
            'apellido_m' => $request->apellido_m,
            'celular'    => $request->celular,
            'email'      => $request->email,
            'activo'     => true,
        ]);

        //Crear usuario
        $usuario = Usuario::create([
            'id_persona' => $persona->id,
            'pass'       => Hash::make($request->password),
            'admin'      => false,
        ]);

        //Enviar correo de verificación
        $usuario->notify(new VerificarEmail());

        return response()->json([
            'message' => 'Registro exitoso. Revisa tu correo para verificar tu cuenta.',
        ], 201);
    }

    // Para verificar email
    public function verifyEmail(Request $request, $id, $hash)
    {
        $usuario = Usuario::findOrFail($id);

        if (!hash_equals(sha1($usuario->getEmailForVerification()), $hash)) {
            return response()->json(['message' => 'Enlace de verificación inválido.'], 400);
        }

        if ($usuario->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo ya fue verificado.']);
        }

        $usuario->markEmailAsVerified();

        return response()->json(['message' => 'Correo verificado correctamente. Ya puedes iniciar sesión.']);
    }

    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $persona = Persona::where('email', $request->email)->firstOrFail();
        $usuario = $persona->usuario;

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        if ($usuario->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo ya fue verificado.']);
        }

        $usuario->notify(new VerificarEmail());

        return response()->json(['message' => 'Correo de verificación reenviado.']);
    }
}