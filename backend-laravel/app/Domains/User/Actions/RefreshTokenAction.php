<?php

declare(strict_types=1);

namespace App\Domains\User\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\User\Services\JwtCookieResponseService;
use App\Domains\User\Support\JwtRequestTokenSupport;
use App\Models\Administrador;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Refresca el token JWT (usuari o admin).
 */
class RefreshTokenAction
{
    private JwtCookieResponseService $jwtResponse;

    public function __construct(JwtCookieResponseService $jwtResponse)
    {
        $this->jwtResponse = $jwtResponse;
    }

    //================================ MÈTODES / FUNCIONS ===========

    public function executar(Request $request): JsonResponse
    {
        $token = JwtRequestTokenSupport::fromRequest($request);
        if ($token === null || $token === '') {
            return response()->json(['message' => 'Token invàlid o expirat'], 401);
        }

        try {
            $nouToken = JWTAuth::setToken($token)->refresh();
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token invàlid o expirat'], 401);
        }

        $payload = JWTAuth::setToken($nouToken)->getPayload();
        $role = $payload->get('role');
        $id = $payload->get('user_id') ?? $payload->get('admin_id') ?? $payload->get('sub');
        if ($role === null || $id === null) {
            return response()->json(['message' => 'Token invàlid'], 401);
        }

        if ($role === 'admin') {
            $admin = Administrador::find((int) $id);
            if ($admin === null) {
                return response()->json(['message' => 'Administrador no trobat'], 401);
            }

            return $this->jwtResponse->crearRespostaRefresh('admin', [
                'id' => $admin->id,
                'nom' => $admin->nom,
                'email' => $admin->email,
            ], $nouToken);
        }

        $usuari = User::find((int) $id);
        if ($usuari === null) {
            return response()->json(['message' => 'Usuari no trobat'], 401);
        }

        return $this->jwtResponse->crearRespostaRefresh('user', [
            'id' => $usuari->id,
            'nom' => $usuari->nom,
            'email' => $usuari->email,
            'monstre_tipus' => $usuari->monstre_tipus,
            'nivell' => $usuari->nivell,
        ], $nouToken);
    }
}
