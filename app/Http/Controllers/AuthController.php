<?php
namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Usuario;
use App\Notifications\VerificarEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\PasswordReset;
use App\Notifications\RecuperarPassword;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_p' => 'required|string|max:100',
            'apellido_m' => 'required|string|max:100',
            'celular' => 'required|numeric',
            'email' => 'required|email|unique:personas,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $persona = Persona::create([
            'nombre' => $request->nombre,
            'apellido_p' => $request->apellido_p,
            'apellido_m' => $request->apellido_m,
            'celular' => $request->celular,
            'email' => $request->email,
            'activo' => true,
        ]);

        $usuario = Usuario::create([
            'id_persona' => $persona->id,
            'pass' => Hash::make($request->password),
            'admin' => false,
        ]);

        $usuario->notify(new VerificarEmail());

        return response()->json([
            'message' => 'Registro exitoso. Revisa tu correo para verificar tu cuenta.',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'dispositivo' => 'required|string|max:100',
        ]);

        // Buscar persona por email
        $persona = Persona::where('email', $request->email)->first();

        if (!$persona || !$persona->usuario) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        $usuario = $persona->usuario;

        // Verificar contraseña
        if (!Hash::check($request->password, $usuario->pass)) {
            return response()->json(['message' => 'Credenciales incorrectas.'], 401);
        }

        // Verificar email confirmado
        if (!$usuario->hasVerifiedEmail()) {
            return response()->json(['message' => 'Debes verificar tu correo electrónico antes de iniciar sesión.'], 403);
        }

        // Un token por dispositiv se elimina token anterior del mismo dispositivo si existe
        $usuario->tokens()->where('name', $request->dispositivo)->delete();

        // Crear nuevo token para este dispositivo
        $token = $usuario->createToken($request->dispositivo)->plainTextToken;

        // Cargar datos del usuario
        $usuario->load('persona');

        return response()->json([
            'token' => $token,
            'usuario' => [
                'id' => $usuario->id,
                'nombre_completo'=> $usuario->persona->nombre_completo,
                'email' => $usuario->persona->email,
                'celular' => (string) $usuario->persona->celular,
                'admin' => $usuario->admin,
                'roles' => $usuario->getRoles(),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        // Elimina solo el token del dispositivo actual
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $usuario = $request->user();

        if (!Hash::check($request->password_actual, $usuario->pass)) {
            return response()->json(['message' => 'La contraseña actual es incorrecta.'], 422);
        }

        // Actualizar contraseña
        $usuario->update(['pass' => Hash::make($request->password)]);

        // Cerrar sesión en TODOS los dispositivos
        $usuario->tokens()->delete();

        return response()->json(['message' => 'Contraseña actualizada. Por seguridad, se cerraron todas las sesiones activas.']);
    }
   

    public function verifyEmail($id, $hash)
{
    $usuario = \App\Models\Usuario::findOrFail($id);

    // Validar hash
    if (!hash_equals(sha1($usuario->getEmailForVerification()), $hash)) {
        return response()->json(['message' => 'Enlace inválido'], 400);
    }

    // Marcar como verificado si no lo está
    if (!$usuario->hasVerifiedEmail()) {
        $usuario->email_verified_at = now(); 
        $usuario->save();
    }

    return response()->json([
        'message' => 'Correo verificado correctamente. Ya puedes iniciar sesión.'
    ]);
}

    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $persona = Persona::where('email', $request->email)->first();

        if (!$persona || !$persona->usuario) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $usuario = $persona->usuario;

        if ($usuario->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo ya fue verificado.']);
        }

        $usuario->notify(new VerificarEmail());

        return response()->json(['message' => 'Correo de verificación reenviado.']);
    }

    public function me(Request $request)
    {
        $usuario = $request->user()->load('persona');

        return response()->json([
            'id' => $usuario->id,
            'nombre_completo'=> $usuario->persona->nombre_completo,
            'email' => $usuario->persona->email,
            'celular' => (string) $usuario->persona->celular,
            'admin' => $usuario->admin,
            'roles' => $usuario->getRoles(),
        ]);
    }

    // Solicitar código
    public function solicitarCodigo(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $persona = Persona::where('email', $request->email)->first();

        if (!$persona || !$persona->usuario) {
            return response()->json([
                'message' => 'Si el correo existe, recibirás un código en breve.',
            ]);
        }

        $usuario = $persona->usuario;

        if (!$usuario->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Debes verificar tu correo antes de recuperar tu contraseña.',
            ], 403);
        }

        PasswordReset::where('email', $request->email)->delete();

        // Generar código de 6 dígitos
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordReset::create([
            'email'      => $request->email,
            'code'       => $code,
            'expires_at' => now()->addMinutes(15),
            'used'       => false,
        ]);

        $usuario->notify(new RecuperarPassword($code));

        return response()->json([
            'message' => 'Si el correo existe, recibirás un código en breve.',
        ]);
    }

    // Verificsr código
    public function verificarCodigo(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        $registro = PasswordReset::where('email', $request->email)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$registro) {
            return response()->json([
                'message' => 'El código es inválido o ha expirado.',
            ], 422);
        }

        return response()->json([
            'message' => 'Código válido.',
        ]);
    }

    // Resetear contraseña
    public function resetearPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $registro = PasswordReset::where('email', $request->email)
            ->where('code', $request->code)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$registro) {
            return response()->json([
                'message' => 'El código es inválido o ha expirado.',
            ], 422);
        }

        $persona = Persona::where('email', $request->email)->first();
        $usuario = $persona?->usuario;

        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        // Actualizar contraseña
        $usuario->update(['pass' => Hash::make($request->password)]);

        // Cerrar todas las sesiones 
        $usuario->tokens()->delete();

        // Eliminar el código usado
        $registro->delete();

        return response()->json([
            'message' => 'Contraseña restablecida correctamente. Ya puedes iniciar sesión.',
        ]);
    }

}
