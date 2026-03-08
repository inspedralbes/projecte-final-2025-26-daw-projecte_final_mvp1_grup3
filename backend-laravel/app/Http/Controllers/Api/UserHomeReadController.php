<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\UserHomeResource;
use App\Services\HomeDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per la pantalla home de l'usuari.
 *
 * Operacions:
 *   - READ: index (dades consolidades: game_state, habits, progress, logros)
 */
class UserHomeReadController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * READ. Retorna les dades consolidades per a la home.
     */
    public function index(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $homeDataService = app(HomeDataService::class);
        $dades = $homeDataService->obtenirDadesHome($usuariId, $request);

        return (new UserHomeResource($dades))->toResponse($request)->header('Cache-Control', 'no-cache');
    }
}
