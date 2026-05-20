<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\User\Actions\GetCurrentUserSessionAction;
use App\Domains\User\Actions\GoogleOAuthCallbackAction;
use App\Domains\User\Actions\LoginUserAction;
use App\Domains\User\Actions\RefreshTokenAction;
use App\Domains\User\Actions\RegisterUserAction;
use App\Domains\User\Services\JwtCookieResponseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador d'autenticació per usuaris (thin).
 *
 * Operacions:
 *   - auth: login, refresh, logout
 *   - CREATE: register
 */
class UserAuthController extends Controller
{
    private LoginUserAction $loginAction;

    private RegisterUserAction $registerAction;

    private RefreshTokenAction $refreshAction;

    private JwtCookieResponseService $jwtResponse;

    private GoogleOAuthCallbackAction $googleCallbackAction;

    private GetCurrentUserSessionAction $currentUserSessionAction;

    public function __construct(
        LoginUserAction $loginAction,
        RegisterUserAction $registerAction,
        RefreshTokenAction $refreshAction,
        JwtCookieResponseService $jwtResponse,
        GoogleOAuthCallbackAction $googleCallbackAction,
        GetCurrentUserSessionAction $currentUserSessionAction
    ) {
        $this->loginAction = $loginAction;
        $this->registerAction = $registerAction;
        $this->refreshAction = $refreshAction;
        $this->jwtResponse = $jwtResponse;
        $this->googleCallbackAction = $googleCallbackAction;
        $this->currentUserSessionAction = $currentUserSessionAction;
    }

    //================================ MÈTODES / FUNCIONS ===========

    public function login(Request $request): JsonResponse
    {
        return $this->loginAction->executar($request);
    }

    public function register(Request $request): JsonResponse
    {
        return $this->registerAction->executar($request);
    }

    public function refresh(Request $request): JsonResponse
    {
        return $this->refreshAction->executar($request);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->currentUserSessionAction->executar($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->jwtResponse->crearRespostaLogout();
    }

    public function redirectToGoogle(): mixed
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function handleGoogleCallback(): mixed
    {
        return $this->googleCallbackAction->executar();
    }
}
