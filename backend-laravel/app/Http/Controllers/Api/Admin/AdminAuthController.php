<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\AuthService;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador d'autenticació per administradors.
 */
class AdminAuthController extends Controller
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
     * Login d'administrador. Valida email i contrasenya, retorna token JWT.
     */
    public function login(Request $request): JsonResponse
    {
        // A. Validar dades d'entrada
        $request->validate([
            'email' => 'required|email',
            'contrasenya' => 'required|string',
        ]);

        // B. Cercar administrador
        $admin = Administrador::where('email', 'ILIKE', $request->input('email'))->first();

        // C. Verificar credencials
        if ($admin === null || !Hash::check($request->input('contrasenya'), $admin->contrasenya_hash)) {
            return response()->json(['message' => 'Credencials incorrectes'], 401);
        }

        // D. Generar token i resposta
        $token = JWTAuth::fromUser($admin);

        return $this->authService->crearRespostaLoginAdmin($admin, $token);
    }
}
