<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\Ratxa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador d'autenticació per usuaris.
 * Login i registre.
 */
class AuthController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Login d'usuari. Valida email i contrasenya, retorna token JWT.
     */
    public function login(Request $request): JsonResponse
    {
        // A. Validació de paràmetres d'entrada
        $request->validate([
            'email' => 'required|email',
            'contrasenya' => 'required|string',
        ]);

        // B. Recuperar usuari pel seu email
        $usuari = User::where('email', $request->input('email'))->first();

        // B1. Validar credencials
        if ($usuari === null || !Hash::check($request->input('contrasenya'), $usuari->contrasenya_hash)) {
            return response()->json(['message' => 'Credencials incorrectes'], 401);
        }

        // B2. Si l'usuari està prohibit, denegar l'accés
        if (!empty($usuari->prohibit)) {
            return response()->json(['message' => 'El compte està prohibit'], 403);
        }

        // C. Generar token JWT
        $token = JWTAuth::fromUser($usuari);

        // D. Retornar resposta
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $usuari->id,
                'nom' => $usuari->nom,
                'email' => $usuari->email,
            ],
        ]);
    }

    /**
     * Registre de nou usuari. Valida camps, crea usuari i RATXES, retorna token (login automàtic).
     */
    public function register(Request $request): JsonResponse
    {
        // A. Validació de paràmetres d'entrada
        $request->validate([
            'nom' => 'required|string|max:100',
            'email' => 'required|email|unique:usuaris,email',
            'contrasenya' => 'required|string|min:6|confirmed',
        ], [
            'contrasenya.confirmed' => 'La contrasenya i la confirmació no coincideixen.',
            'email.unique' => 'Aquest email ja està registrat.',
        ]);

        // B. Crear l'usuari
        $usuari = User::create([
            'nom' => $request->input('nom'),
            'email' => $request->input('email'),
            'contrasenya_hash' => Hash::make($request->input('contrasenya')),
        ]);

        // C. Crear ratxa inicial
        Ratxa::create([
            'usuari_id' => $usuari->id,
            'ratxa_actual' => 0,
            'ratxa_maxima' => 0,
        ]);

        // D. Generar token JWT
        $token = JWTAuth::fromUser($usuari);

        // E. Retornar resposta
        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $usuari->id,
                'nom' => $usuari->nom,
                'email' => $usuari->email,
            ],
        ], 201);
    }
}
