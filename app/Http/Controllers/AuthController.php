<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $hashedPassword = hash('sha256', trim($request->password));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $hashedPassword,
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['user' => $user, 'access_token' => $token, 'token_type' => 'Bearer'], 201);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Buscamos al usuario por email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized: User not found'], 401);
        }

        // Hashear la contraseña ingresada usando SHA-256
        $hashedPassword = hash('sha256', trim($request->password));

        // Comparar ambos hashes
        if (!hash_equals($hashedPassword, $user->password)) {
            return response()->json([
                'message' => 'Unauthorized: Incorrect password',
                'entered_hash' => $hashedPassword,
                'stored_hash' => $user->password
            ], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['message' => 'Login successful', 'access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function logout(Request $request)
    {
        // 1. Asegurarse de que el usuario esté autenticado.
        //    Aquí obtenemos el usuario que está actualmente autenticado a través del token proporcionado.
        $user = $request->user();

        // 2. Comprobamos si el usuario está autenticado. Si no lo está, devolvemos un mensaje de error.
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // 3. Eliminamos todos los tokens del usuario autenticado, lo que implica que se desloguea de todas las sesiones activas.
        $user->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
