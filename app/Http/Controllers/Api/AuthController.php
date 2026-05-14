<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validamos que envíen correo y contraseña
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Intentamos autenticar con las credenciales
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciales incorrectas. Verifica tu correo o contraseña.'
            ], 401); // 401: No autorizado
        }

        // 3. Si es correcto, buscamos al usuario
        $user = User::where('email', $request->email)->firstOrFail();

        // 4. Generamos el Token de acceso con Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Devolvemos la información al frontend (React)
        return response()->json([
            'message' => 'Login exitoso',
            'user' => $user, // Envía los datos del usuario (sin el password)
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }

    public function logout(Request $request)
    {
        // Revocamos (borramos) el token actual que está usando el usuario
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    public function me(Request $request)
    {
        // Devuelve los datos del usuario que está haciendo la petición
        return response()->json($request->user());
    }

    public function register(Request $request)
    {
        // 1. Validamos los datos de entrada
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6', // Mínimo 6 caracteres
        ]);

        // 2. Creamos el usuario encriptando la contraseña
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Encriptación segura
        ]);

        // 3. (Opcional) Podemos generar el token de una vez para loguearlo directamente
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Devolvemos la respuesta
        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 201); // 201: Creado
    }
}