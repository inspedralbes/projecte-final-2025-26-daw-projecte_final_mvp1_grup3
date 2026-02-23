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
     * C. Retornar resposta JSON (xp_total, ratxa_actual, ratxa_maxima, monedes, missio_diaria, missio_completada).
     */
    public function show(): JsonResponse
    {
        $usuariId = 1;

        try {
            // A. Obtenir estat de gamificació des del servei
            $estat = $this->gamificationService->obtenirEstatGamificacio($usuariId);

            // B. Retornar resposta JSON amb headers
            return response()->json($estat, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ]);
        } catch (\Throwable $e) {
            report($e);

            // C. En cas d'excepció, retornar valors per defecte per no trencar el frontend
            return response()->json([
                'error' => 'Error carregant estat del joc',
                'usuari_id' => $usuariId,
                'xp_total' => 0,
                'ratxa_actual' => 0,
                'ratxa_maxima' => 0,
                'monedes' => 0,
                'missio_diaria' => null,
                'missio_completada' => false,
            ], 200);
        }
    }
}
