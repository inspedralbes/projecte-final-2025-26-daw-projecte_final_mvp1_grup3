<?php

declare(strict_types=1);

namespace App\Domains\User\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\User\Services\JwtCookieResponseService;
use App\Models\Ratxa;
use App\Models\User;
use App\Services\WelcomeEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Registre d'un usuari nou amb ratxa inicial.
 */
class RegisterUserAction
{
    private JwtCookieResponseService $jwtResponse;

    private WelcomeEmailService $welcomeEmailService;

    public function __construct(
        JwtCookieResponseService $jwtResponse,
        WelcomeEmailService $welcomeEmailService
    ) {
        $this->jwtResponse = $jwtResponse;
        $this->welcomeEmailService = $welcomeEmailService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    public function executar(Request $request): JsonResponse
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
            'nivell' => 1,
            'xp_total' => 0,
            'xp_actual_nivel' => 0,
            'xp_objetivo_nivel' => 1000,
            'monedes' => 0,
            'missio_completada' => false,
        ]);

        Ratxa::create([
            'usuari_id' => $usuari->id,
            'ratxa_actual' => 0,
            'ratxa_maxima' => 0,
        ]);

        $token = JWTAuth::fromUser($usuari);
        $this->welcomeEmailService->enviarSiPrimeraConnexio($usuari);

        $resposta = $this->jwtResponse->crearRespostaLoginUsuari($usuari, $token, true);

        return $resposta->setStatusCode(201);
    }
}
