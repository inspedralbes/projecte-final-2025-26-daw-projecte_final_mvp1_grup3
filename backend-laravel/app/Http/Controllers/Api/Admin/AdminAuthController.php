<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador d'autenticació per administradors.
 */
class AdminAuthController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Login d'administrador. Valida email i contrasenya, retorna token JWT.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'contrasenya' => 'required|string',
        ]);

        $admin = Administrador::where('email', 'ILIKE', $request->input('email'))->first();

        if ($admin === null || !Hash::check($request->input('contrasenya'), $admin->contrasenya_hash)) {
            return response()->json(['message' => 'Credencials incorrectes'], 401);
        }

        $token = JWTAuth::fromUser($admin);

        return response()->json([
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'nom' => $admin->nom,
                'email' => $admin->email,
            ],
        ]);
    }
}
