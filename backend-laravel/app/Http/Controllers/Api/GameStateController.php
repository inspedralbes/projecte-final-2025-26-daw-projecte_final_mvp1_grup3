<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * Retorna l'estat de gamificació de l'usuari autenticat.
     */
    public function show(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        // A. Si no hi ha usuari, denegar accés
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        // B. Intentar obtenir l'estat de gamificació
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
