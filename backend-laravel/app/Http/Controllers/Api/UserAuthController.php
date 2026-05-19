<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Ratxa;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserProhibitionService;
use App\Services\WelcomeEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador d'autenticació per usuaris.
 *
 * Operacions:
 *   - auth: login, refresh, logout
 *   - CREATE: register (crea usuari + ratxes, retorna token)
 */
class UserAuthController extends Controller
{
    //================================ PROPIETATS / ATRIBUTS ==========

    private AuthService $authService;

    private WelcomeEmailService $welcomeEmailService;

    public function __construct(
        AuthService $authService,
        WelcomeEmailService $welcomeEmailService,
        private UserProhibitionService $prohibitionService
    ) {
        $this->authService = $authService;
        $this->welcomeEmailService = $welcomeEmailService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * auth. Valida credencials, retorna JWT.
     */
    public function login(Request $request): JsonResponse
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

        return $this->authService->crearRespostaLoginUsuari($usuari, $token, $usuari->necessitaOnboarding());
    }

    /**
     * CREATE. Crea usuari + ratxes, retorna token (login automàtic).
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

        $resposta = $this->authService->crearRespostaLoginUsuari($usuari, $token, true);

        return $resposta->setStatusCode(201);
    }

    /**
     * auth. Refresca token (user o admin).
     */
    public function refresh(Request $request): JsonResponse
    {
        $token = null;
        $authHeader = $request->header('Authorization');
        if (is_string($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }
        if ($token === null || $token === '') {
            $cookieNom = (string) config('jwt.cookie', 'loopy_token');
            $token = $request->cookie($cookieNom);
        }
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
            return $this->authService->crearRespostaRefresh('admin', [
                'id' => $admin->id,
                'nom' => $admin->nom,
                'email' => $admin->email,
            ], $nouToken);
        }

        $usuari = User::find((int) $id);
        if ($usuari === null) {
            return response()->json(['message' => 'Usuari no trobat'], 401);
        }

        return $this->authService->crearRespostaRefresh('user', [
            'id' => $usuari->id,
            'nom' => $usuari->nom,
            'email' => $usuari->email,
            'monstre_tipus' => $usuari->monstre_tipus,
            'nivell' => $usuari->nivell,
        ], $nouToken);
    }

    /**
     * Esborra cookies d'autenticació.
     */
    public function logout(Request $request): JsonResponse
    {
        return $this->authService->crearRespostaLogout();
    }

    /**
     * Redirigeix a Google per autenticació.
     */
    public function redirectToGoogle(): mixed
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    /**
     * Gestiona el callback de Google OAuth.
     */
    public function handleGoogleCallback(): mixed
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $usuari = User::where('google_id', $googleUser->getId())->first();

            if (!$usuari) {
                $usuari = User::where('email', 'ILIKE', $googleUser->getEmail())->first();

                if ($usuari) {
                    $usuari->update(['google_id' => (string) $googleUser->getId()]);
                } else {
                    $usuari = User::create([
                        'nom'       => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Google User',
                        'email'     => $googleUser->getEmail(),
                        'google_id' => (string) $googleUser->getId(),
                    ]);

                    Ratxa::create([
                        'usuari_id'    => $usuari->id,
                        'ratxa_actual' => 0,
                        'ratxa_maxima' => 0,
                    ]);
                }
            }

            $requiresOnboarding = $usuari->necessitaOnboarding();

            $respostaBan = $this->respostaSiUsuariProhibit($usuari);
            if ($respostaBan !== null) {
                return $respostaBan;
            }

            $token = JWTAuth::fromUser($usuari);

            $this->welcomeEmailService->enviarSiPrimeraConnexio($usuari);

            $frontendUrl = env('GOOGLE_FRONTEND_REDIRECT', 'http://localhost:3000/auth/google/redirect');
            $redirectUrl = $frontendUrl . '?token=' . $token . '&onboarding=' . ($requiresOnboarding ? '1' : '0');

            $resposta = redirect($redirectUrl);
            return $this->authService->attachAuthCookies($resposta, $token, 'user');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error Google Login: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error en el login amb Google',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resposta 403 amb detall del ban, o null si pot continuar el login.
     */
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
