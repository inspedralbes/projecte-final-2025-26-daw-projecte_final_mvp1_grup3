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
        $request->validate([
            'email' => 'required|email',
            'contrasenya' => 'required|string',
        ]);

        $usuari = User::where('email', $request->input('email'))->first();

        if ($usuari === null || !Hash::check($request->input('contrasenya'), $usuari->contrasenya_hash)) {
            return response()->json(['message' => 'Credencials incorrectes'], 401);
        }

        if (!empty($usuari->prohibit)) {
            return response()->json(['message' => 'El compte està prohibit'], 403);
        }

        $token = JWTAuth::fromUser($usuari);

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
        $request->validate([
            'nom' => 'required|string|max:100',
            'email' => 'required|email|unique:usuaris,email',
            'contrasenya' => 'required|string|min:6|confirmed',
        ], [
            'contrasenya.confirmed' => 'La contrasenya i la confirmació no coincideixen.',
            'email.unique' => 'Aquest email ja està registrat.',
        ]);

        $usuari = User::create([
            'nom' => $request->input('nom'),
            'email' => $request->input('email'),
            'contrasenya_hash' => Hash::make($request->input('contrasenya')),
        ]);

        Ratxa::create([
            'usuari_id' => $usuari->id,
            'ratxa_actual' => 0,
            'ratxa_maxima' => 0,
        ]);

        $token = JWTAuth::fromUser($usuari);

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
