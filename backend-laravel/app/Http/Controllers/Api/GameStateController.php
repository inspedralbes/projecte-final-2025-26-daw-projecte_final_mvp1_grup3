<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per l'estat de gamificació.
 */
class GameStateController extends Controller
{
    /**
     * Servei de gamificació.
     *
     * @var GamificationService
     */
    protected GamificationService $gamificationService;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Constructor. Injecció del servei de gamificació.
     */
    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Retorna l'estat de gamificació de l'usuari per defecte (id 1).
     *
     * A. Definir usuari per defecte.
     * B. Obtenir estat de gamificació des del servei.
     * C. Retornar resposta JSON.
     */
    public function show(): JsonResponse
    {
        // A. Usuari per defecte sense autenticació (id 1)
        $usuariId = 1;

        // B. Obtenir estat de gamificació
        $estat = $this->gamificationService->obtenirEstatGamificacio($usuariId);

        // C. Retornar resposta
        return response()->json($estat);
    }
}
