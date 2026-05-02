<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\Ratxa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per el perfil de l'usuari.
 *
 * Operacions:
 *   - READ: profile (perfil amb XP, ratxa, logros)
 */
class UserProfileReadController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * READ. Retorna el perfil de l'usuari autenticat amb XP, ratxa i logros.
     */
    public function profile(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;

        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        // comprovarLogros diferit: es crida quan es completa un hàbit (HabitService).
        // Evita la lentitud de ~20-40 queries SQL en cada càrrega del perfil.
        $usuari = User::with('logros')->find($usuariId);

        if (!$usuari) {
            return response()->json(['error' => 'Usuari no trobat'], 404);
        }

        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();

        $ratxaActual = 0;
        if ($ratxa) {
            $ratxaActual = (int) $ratxa->ratxa_actual;
        }

        $ratxaMaxima = 0;
        if ($ratxa) {
            $ratxaMaxima = (int) $ratxa->ratxa_maxima;
        }

        $usuari->ratxa_actual = $ratxaActual;
        $usuari->ratxa_maxima = $ratxaMaxima;

        return (new UserProfileResource($usuari))->toResponse($request);
    }
}
