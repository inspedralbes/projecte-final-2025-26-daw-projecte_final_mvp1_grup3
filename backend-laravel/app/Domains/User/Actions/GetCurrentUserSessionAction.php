<?php

declare(strict_types=1);

namespace App\Domains\User\Actions;

use App\Domains\User\Services\JwtCookieResponseService;
use App\Domains\User\Support\JwtRequestTokenSupport;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Retorna la sessió de l'usuari autenticat sense rotar el JWT (per OAuth Google al client).
 */
class GetCurrentUserSessionAction
{
    private JwtCookieResponseService $jwtResponse;

    public function __construct(JwtCookieResponseService $jwtResponse)
    {
        $this->jwtResponse = $jwtResponse;
    }

    public function executar(Request $request): JsonResponse
    {
        $userId = (int) ($request->input('user_id') ?? $request->user_id ?? 0);
        if ($userId <= 0) {
            return response()->json(['message' => 'No autentificat'], 401);
        }

        $usuari = User::find($userId);
        if ($usuari === null) {
            return response()->json(['message' => 'Usuari no trobat'], 404);
        }

        $token = JwtRequestTokenSupport::fromRequest($request);
        if ($token === null || $token === '') {
            return response()->json(['message' => 'Token invàlid o expirat'], 401);
        }

        return $this->jwtResponse->crearRespostaRefresh('user', [
            'id' => $usuari->id,
            'nom' => $usuari->nom,
            'email' => $usuari->email,
            'monstre_tipus' => $usuari->monstre_tipus,
            'nivell' => $usuari->nivell,
        ], $token);
    }
}
