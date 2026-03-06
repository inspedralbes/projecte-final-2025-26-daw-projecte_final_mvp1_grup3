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
    protected $logroService;

    /**
     * Constructor per injectar serveis.
     */
    public function __construct(\App\Services\LogroService $logroService)
    {
        $this->logroService = $logroService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna el perfil de l'usuari autenticat amb els seus logros i dades de gamificació.
     */
    public function profile(Request $request): JsonResponse
    {
        // A. Recuperació de l'identificador d'usuari des del request
        $usuariId = $request->user_id;

        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        // B. Comprovar i atorgar logros pendents (ex: Primer Encuentro)
        $this->logroService->comprovarLogros($usuariId);

        // C. Cercar usuari i carregar relacions de logros
        $usuari = User::with('logros')->find($usuariId);

        if (!$usuari) {
            return response()->json(['error' => 'Usuari no trobat'], 404);
        }

        // D. Obtenir dades de la ratxa actual
        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();
        
        $ratxaActual = 0;
        if ($ratxa) {
            $ratxaActual = (int) $ratxa->ratxa_actual;
        }

        $ratxaMaxima = 0;
        if ($ratxa) {
            $ratxaMaxima = (int) $ratxa->ratxa_maxima;
        }

        // E. Retornar resposta JSON amb el format requerit pel frontend
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
