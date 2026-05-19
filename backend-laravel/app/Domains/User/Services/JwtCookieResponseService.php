<?php

declare(strict_types=1);

namespace App\Domains\User\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Administrador;
use App\Models\User;
use Illuminate\Http\JsonResponse;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Respostes HTTP amb cookies JWT per usuaris i administradors.
 */
class JwtCookieResponseService
{
    private string $cookieNom;

    private string $roleCookieNom;

    private int $cookieMinuts;

    private bool $cookieSegur;

    private string $sameSite;

    public function __construct()
    {
        $this->cookieNom = (string) config('jwt.cookie', 'loopy_token');
        $this->roleCookieNom = 'loopy_role';
        $this->cookieMinuts = (int) config('jwt.refresh_ttl', 20160);
        $this->cookieSegur = (string) config('app.env') === 'production';
        $this->sameSite = 'lax';
    }

    //================================ MÈTODES / FUNCIONS ===========

    public function crearRespostaLoginUsuari(User $usuari, string $token, bool $requiresOnboarding = false): JsonResponse
    {
        $dades = [
            'token' => $token,
            'role' => 'user',
            'requires_onboarding' => $requiresOnboarding,
            'user' => [
                'id' => $usuari->id,
                'nom' => $usuari->nom,
                'email' => $usuari->email,
                'monstre_tipus' => $usuari->monstre_tipus,
                'nivell' => $usuari->nivell,
            ],
        ];

        return $this->crearRespostaAmbCookies($dades, 'user');
    }

    public function crearRespostaLoginAdmin(Administrador $admin, string $token): JsonResponse
    {
        $dades = [
            'token' => $token,
            'role' => 'admin',
            'admin' => [
                'id' => $admin->id,
                'nom' => $admin->nom,
                'email' => $admin->email,
            ],
        ];

        return $this->crearRespostaAmbCookies($dades, 'admin');
    }

    /**
     * @param  array<string, mixed>  $dadesPerfil
     */
    public function crearRespostaRefresh(string $role, array $dadesPerfil, string $token): JsonResponse
    {
        $dades = [
            'token' => $token,
            'role' => $role,
        ];

        if ($role === 'admin') {
            $dades['admin'] = $dadesPerfil;
        } else {
            $dades['user'] = $dadesPerfil;
            $monstre = $dadesPerfil['monstre_tipus'] ?? null;
            $dades['requires_onboarding'] = $monstre === null || $monstre === '';
        }

        return $this->crearRespostaAmbCookies($dades, $role);
    }

    public function crearRespostaLogout(): JsonResponse
    {
        $resposta = response()->json(['message' => 'Logout correcte']);
        $resposta->cookie(
            $this->cookieNom,
            '',
            -1,
            '/',
            null,
            $this->cookieSegur,
            true,
            false,
            $this->sameSite
        );
        $resposta->cookie(
            $this->roleCookieNom,
            '',
            -1,
            '/',
            null,
            $this->cookieSegur,
            false,
            false,
            $this->sameSite
        );

        return $resposta;
    }

    /**
     * @param  array<string, mixed>  $dades
     */
    private function crearRespostaAmbCookies(array $dades, string $role): JsonResponse
    {
        $resposta = response()->json($dades);

        return $this->attachAuthCookies($resposta, $dades['token'], $role);
    }

    /**
     * @param  mixed  $resposta
     * @return mixed
     */
    public function attachAuthCookies($resposta, string $token, string $role)
    {
        $resposta->cookie(
            $this->cookieNom,
            $token,
            $this->cookieMinuts,
            '/',
            null,
            $this->cookieSegur,
            true,
            false,
            $this->sameSite
        );

        $resposta->cookie(
            $this->roleCookieNom,
            $role,
            $this->cookieMinuts,
            '/',
            null,
            $this->cookieSegur,
            false,
            false,
            $this->sameSite
        );

        return $resposta;
    }
}
