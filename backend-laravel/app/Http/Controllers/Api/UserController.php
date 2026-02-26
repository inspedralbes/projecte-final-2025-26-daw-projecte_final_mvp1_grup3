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
        // A. Recuperar usuari_id del request
        $usuariId = (int) $request->user_id;
        // A1. Si no hi ha usuari, retornar error
        if ($usuariId <= 0) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        // B. Cercar usuari i carregar relacions
        $usuari = User::with('logros')->find($usuariId);

        // B1. Si l'usuari no existeix, retornar 404
        if (!$usuari) {
            return response()->json(['error' => 'Usuari no trobat'], 404);
        }

        // C. Obtenir ratxa actual
        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();
        // C1. Si hi ha ratxa, normalitzar valors
        if ($ratxa) {
            $ratxaActual = (int) $ratxa->ratxa_actual;
            $ratxaMaxima = (int) $ratxa->ratxa_maxima;
        } else {
            $ratxaActual = 0;
            $ratxaMaxima = 0;
        }

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
