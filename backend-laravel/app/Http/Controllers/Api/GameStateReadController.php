<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\GameStateResource;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per l'estat de gamificació.
 *
 * Operacions:
 *   - READ: show (XP, nivell, ratxa, monedes, missió)
 */
class GameStateReadController extends Controller
{
    /**
     * Servei de gamificació.
     *
     * @var GamificationService
     */
    protected GamificationService $gamificationService;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Constructor. Injecció del servei.
     */
    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * READ. Retorna l'estat de gamificació de l'usuari autenticat.
     */
    public function show(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        try {
            $estat = $this->gamificationService->obtenirEstatGamificacio($usuariId);

            return (new GameStateResource($estat))->toResponse($request)->setStatusCode(200)->header('Cache-Control', 'no-cache');
        } catch (\Throwable $e) {
            report($e);

            $estatError = [
                'usuari_id' => $usuariId,
                'xp_total' => 0,
                'nivell' => 1,
                'xp_actual_nivel' => 0,
                'xp_objetivo_nivel' => 1000,
                'ratxa_actual' => 0,
                'ratxa_maxima' => 0,
                'monedes' => 0,
                'missio_diaria' => null,
                'missio_completada' => false,
            ];

            return (new GameStateResource($estatError))->toResponse($request)->setStatusCode(200)->header('Cache-Control', 'no-cache');
        }
    }
}
