<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Services\HomeDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per la pantalla home de l'usuari.
 * Retorna totes les dades consolidades (game_state, habits, progress, logros).
 */
class UserHomeController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna les dades consolidades per a la home.
     */
    public function index(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $homeDataService = app(HomeDataService::class);
        $dades = $homeDataService->obtenirDadesHome($usuariId, $request);

        return response()->json($dades, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
