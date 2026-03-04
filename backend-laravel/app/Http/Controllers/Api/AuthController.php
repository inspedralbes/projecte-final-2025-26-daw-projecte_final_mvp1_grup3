<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\Ratxa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\AuthService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador d'autenticació per usuaris.
 * Login i registre.
 */
class AuthController extends Controller
{
    //================================ PROPIETATS / ATRIBUTS ==========

    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        // A. Assignar servei d'autenticació
        $this->authService = $authService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Login d'usuari. Valida email i contrasenya, retorna token JWT.
     */
    public function login(Request $request): JsonResponse
    {
        // A. Validar dades d'entrada
        $request->validate([
            'email' => 'required|email',
            'contrasenya' => 'required|string',
        ]);

        // B. Cercar usuari per email
        $usuari = User::where('email', 'ILIKE', $request->input('email'))->first();

        // C. Verificar credencials
        if ($usuari === null || !Hash::check($request->input('contrasenya'), $usuari->contrasenya_hash)) {
            return response()->json(['message' => 'Credencials incorrectes'], 401);
        }

        // D. Comprovar si el compte està prohibit
        if (!empty($usuari->prohibit)) {
            return response()->json(['message' => 'El compte està prohibit'], 403);
        }

        // E. Generar token i resposta
        $token = JWTAuth::fromUser($usuari);

        return $this->authService->crearRespostaLoginUsuari($usuari, $token);
    }

    /**
     * Registre de nou usuari. Valida camps, crea usuari i RATXES, retorna token (login automàtic).
     */
    public function register(Request $request): JsonResponse
    {
        // A. Validar dades d'entrada
        $request->validate([
            'nom' => 'required|string|max:100',
            'email' => 'required|email|unique:usuaris,email',
            'contrasenya' => 'required|string|min:6|confirmed',
        ], [
            'contrasenya.confirmed' => 'La contrasenya i la confirmació no coincideixen.',
            'email.unique' => 'Aquest email ja està registrat.',
        ]);

        // B. Crear usuari
        $usuari = User::create([
            'nom' => $request->input('nom'),
            'email' => $request->input('email'),
            'contrasenya_hash' => Hash::make($request->input('contrasenya')),
        ]);

        // C. Crear ratxes inicials
        Ratxa::create([
            'usuari_id' => $usuari->id,
            'ratxa_actual' => 0,
            'ratxa_maxima' => 0,
        ]);

        // D. Generar token i resposta
        $token = JWTAuth::fromUser($usuari);

        $resposta = $this->authService->crearRespostaLoginUsuari($usuari, $token);

        return $resposta->setStatusCode(201);
    }

    /**
     * Refresh de token. Retorna nou token i dades bàsiques.
     * Llegeix el token del header Authorization o de la cookie loopy_token (per refresh tras F5).
     */
    public function refresh(Request $request): JsonResponse
    {
        // A. Obtenir token: header Bearer o cookie (necesari quan el frontend fa refresh i perd el token en memòria)
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

        // B. Refrescar token i gestionar errors
        try {
            $nouToken = JWTAuth::setToken($token)->refresh();
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token invàlid o expirat'], 401);
        }

        // B. Extreure rol i id del payload
        $payload = JWTAuth::setToken($nouToken)->getPayload();
        $role = $payload->get('role');
        $id = $payload->get('user_id') ?? $payload->get('admin_id') ?? $payload->get('sub');
        // B1. Validar rol i identificador
        if ($role === null || $id === null) {
            return response()->json(['message' => 'Token invàlid'], 401);
        }

        // C. Retornar resposta segons rol
        // C0. Comprovar si és administrador
        if ($role === 'admin') {
            // C1. Cercar administrador
            $admin = Administrador::find((int) $id);
            // C2. Validar administrador existent
            if ($admin === null) {
                return response()->json(['message' => 'Administrador no trobat'], 401);
            }
            return $this->authService->crearRespostaRefresh('admin', [
                'id' => $admin->id,
                'nom' => $admin->nom,
                'email' => $admin->email,
            ], $nouToken);
        }

        // C2. Cercar usuari
        $usuari = User::find((int) $id);
        // C3. Validar usuari existent
        if ($usuari === null) {
            return response()->json(['message' => 'Usuari no trobat'], 401);
        }

        return $this->authService->crearRespostaRefresh('user', [
            'id' => $usuari->id,
            'nom' => $usuari->nom,
            'email' => $usuari->email,
        ], $nouToken);
    }

    /**
     * Logout. Esborra cookies d'autenticació.
     */
    public function logout(Request $request): JsonResponse
    {
        // A. Retornar resposta de logout i esborrar cookies
        return $this->authService->crearRespostaLogout();
    }
}
