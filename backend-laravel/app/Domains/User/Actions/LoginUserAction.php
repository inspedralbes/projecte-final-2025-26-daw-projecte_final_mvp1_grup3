<?php

declare(strict_types=1);

namespace App\Domains\User\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\User\Services\JwtCookieResponseService;
use App\Models\User;
use App\Domains\Admin\Services\UserProhibitionService;
use App\Domains\User\Services\WelcomeEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Login d'usuari amb email i contrasenya.
 */
class LoginUserAction
{
    private JwtCookieResponseService $jwtResponse;

    private WelcomeEmailService $welcomeEmailService;

    private UserProhibitionService $prohibitionService;

    public function __construct(
        JwtCookieResponseService $jwtResponse,
        WelcomeEmailService $welcomeEmailService,
        UserProhibitionService $prohibitionService
    ) {
        $this->jwtResponse = $jwtResponse;
        $this->welcomeEmailService = $welcomeEmailService;
        $this->prohibitionService = $prohibitionService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    public function executar(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'contrasenya' => 'required|string',
        ]);

        $usuari = User::where('email', 'ILIKE', $request->input('email'))->first();

        if ($usuari === null || !Hash::check($request->input('contrasenya'), $usuari->contrasenya_hash)) {
            return response()->json(['message' => 'Credencials incorrectes'], 401);
        }

        $respostaBan = $this->respostaSiUsuariProhibit($usuari);
        if ($respostaBan !== null) {
            return $respostaBan;
        }

        $token = JWTAuth::fromUser($usuari);
        $this->welcomeEmailService->enviarSiPrimeraConnexio($usuari);

        return $this->jwtResponse->crearRespostaLoginUsuari($usuari, $token, $usuari->necessitaOnboarding());
    }

    private function respostaSiUsuariProhibit(User $usuari): ?JsonResponse
    {
        if (empty($usuari->prohibit)) {
            return null;
        }

        $info = $this->prohibitionService->evaluarProhibicio($usuari);
        if ($info === null) {
            $usuari->refresh();

            return null;
        }

        return response()->json([
            'message' => 'El compte està prohibit',
            'code' => 'account_banned',
            'ban' => $info,
        ], 403);
    }
}

