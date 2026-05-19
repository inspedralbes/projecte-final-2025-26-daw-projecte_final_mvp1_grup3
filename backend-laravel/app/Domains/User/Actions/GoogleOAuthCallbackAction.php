<?php

declare(strict_types=1);

namespace App\Domains\User\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\User\Services\JwtCookieResponseService;
use App\Models\Ratxa;
use App\Models\User;
use App\Domains\Admin\Services\UserProhibitionService;
use App\Domains\User\Services\WelcomeEmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Callback OAuth de Google per a usuaris.
 */
class GoogleOAuthCallbackAction
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

    /**
     * @return mixed
     */
    public function executar()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $usuari = User::where('google_id', $googleUser->getId())->first();

            if (!$usuari) {
                $usuari = User::where('email', 'ILIKE', $googleUser->getEmail())->first();

                if ($usuari) {
                    $usuari->update(['google_id' => (string) $googleUser->getId()]);
                } else {
                    $nom = $googleUser->getName();
                    if ($nom === null || $nom === '') {
                        $nom = $googleUser->getNickname();
                    }
                    if ($nom === null || $nom === '') {
                        $nom = 'Google User';
                    }

                    $usuari = User::create([
                        'nom' => $nom,
                        'email' => $googleUser->getEmail(),
                        'google_id' => (string) $googleUser->getId(),
                    ]);

                    Ratxa::create([
                        'usuari_id' => $usuari->id,
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
            $onboardingFlag = '0';
            if ($requiresOnboarding) {
                $onboardingFlag = '1';
            }
            $redirectUrl = $frontendUrl . '?token=' . $token . '&onboarding=' . $onboardingFlag;

            $resposta = redirect($redirectUrl);

            return $this->jwtResponse->attachAuthCookies($resposta, $token, 'user');
        } catch (\Exception $e) {
            Log::error('Error Google Login: ' . $e->getMessage());

            return response()->json([
                'message' => 'Error en el login amb Google',
                'error' => $e->getMessage(),
            ], 500);
        }
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

