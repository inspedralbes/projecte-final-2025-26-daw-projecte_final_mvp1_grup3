<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Administrador;
use App\Models\User;
use Illuminate\Http\JsonResponse;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei d'autenticació per crear respostes amb cookies JWT.
 */
class AuthService
{
    private string $cookieNom;

    private string $roleCookieNom;

    private int $cookieMinuts;

    private bool $cookieSegur;

    private string $sameSite;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct()
    {
        // A. Preparar configuració base de cookies
        $this->cookieNom = (string) config('jwt.cookie', 'loopy_token');
        $this->roleCookieNom = 'loopy_role';
        $this->cookieMinuts = (int) config('jwt.refresh_ttl', 20160);
        $this->cookieSegur = (string) config('app.env') === 'production';
        $this->sameSite = 'lax';
    }

    /**
     * Crea resposta de login per usuari.
     */
    public function crearRespostaLoginUsuari(User $usuari, string $token): JsonResponse
    {
        // A. Preparar payload d'usuari
        $dades = [
            'token' => $token,
            'role' => 'user',
            'user' => [
                'id' => $usuari->id,
                'nom' => $usuari->nom,
                'email' => $usuari->email,
            ],
        ];

        // B. Crear resposta amb cookies
        return $this->crearRespostaAmbCookies($dades, 'user');
    }

    /**
     * Crea resposta de login per admin.
     */
    public function crearRespostaLoginAdmin(Administrador $admin, string $token): JsonResponse
    {
        // A. Preparar payload d'administrador
        $dades = [
            'token' => $token,
            'role' => 'admin',
            'admin' => [
                'id' => $admin->id,
                'nom' => $admin->nom,
                'email' => $admin->email,
            ],
        ];

        // B. Crear resposta amb cookies
        return $this->crearRespostaAmbCookies($dades, 'admin');
    }

    /**
     * Crea resposta de refresh segons el rol.
     *
     * @param  array<string, mixed>  $dadesPerfil
     */
    public function crearRespostaRefresh(string $role, array $dadesPerfil, string $token): JsonResponse
    {
        // A. Preparar dades de resposta bàsiques
        $dades = [
            'token' => $token,
            'role' => $role,
        ];
        // B. Assignar perfil segons rol
        if ($role === 'admin') {
            $dades['admin'] = $dadesPerfil;
        } else {
            $dades['user'] = $dadesPerfil;
        }

        // C. Retornar resposta amb cookies
        return $this->crearRespostaAmbCookies($dades, $role);
    }

    /**
     * Crea resposta de logout amb cookies esborrades.
     */
    public function crearRespostaLogout(): JsonResponse
    {
        // A. Crear resposta base de logout
        $resposta = response()->json(['message' => 'Logout correcte']);
        // B. Esborrar cookie del token
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
        // C. Esborrar cookie de rol
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
     * Crea resposta amb cookies (token + rol).
     *
     * @param  array<string, mixed>  $dades
     */
    private function crearRespostaAmbCookies(array $dades, string $role): JsonResponse
    {
        // A. Crear resposta amb payload
        $resposta = response()->json($dades);
        // B. Assignar cookie del token
        $resposta->cookie(
            $this->cookieNom,
            $dades['token'],
            $this->cookieMinuts,
            '/',
            null,
            $this->cookieSegur,
            true,
            false,
            $this->sameSite
        );
        // C. Assignar cookie de rol
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

        // D. Retornar resposta final
        return $resposta;
    }
}
