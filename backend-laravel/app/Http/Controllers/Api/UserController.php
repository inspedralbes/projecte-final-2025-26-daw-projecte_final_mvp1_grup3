<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ratxa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per a la gestió d'usuaris.
 */
class UserController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna el perfil de l'usuari autenticat amb els seus logros i dades de gamificació.
     */
    public function profile(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        // B. Cercar usuari i carregar relacions
        $usuari = User::with('logros')->find($usuariId);

        if (!$usuari) {
            return response()->json(['error' => 'Usuari no trobat'], 404);
        }

        // C. Obtenir ratxa actual
        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();
        $ratxaActual = $ratxa ? (int) $ratxa->ratxa_actual : 0;
        $ratxaMaxima = $ratxa ? (int) $ratxa->ratxa_maxima : 0;

        // D. Retornar resposta JSON
        return response()->json([
            'id' => $usuari->id,
            'nom' => $usuari->nom,
            'email' => $usuari->email,
            'nivell' => (int) $usuari->nivell,
            'xp_total' => (int) $usuari->xp_total,
            'monedes' => (int) $usuari->monedes,
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
            'logros' => $usuari->logros->map(function ($logro) {
                return [
                    'id' => $logro->id,
                    'nom' => $logro->nom,
                    'descripcio' => $logro->descripcio,
                    'tipus' => $logro->tipus,
                    'data_obtencio' => $logro->pivot->data_obtencio,
                ];
            }),
        ]);
    }
}
