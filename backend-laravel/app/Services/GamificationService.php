<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Ratxa;
use App\Models\User;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de gamificació.
 * Centralitza la lectura d'XP i ratxes de l'usuari.
 */
class GamificationService
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Obté l'estat de gamificació d'un usuari.
     *
     * A. Recuperar l'usuari i validar que existeix.
     * B. Recuperar la ratxa associada (si existeix).
     * C. Retornar els valors normalitzats per al frontend.
     *
     * @param  int  $usuariId
     * @return array<string, int>
     */
    public function obtenirEstatGamificacio(int $usuariId): array
    {
        // A. Recuperar usuari
        $usuari = User::find($usuariId);

        if ($usuari === null) {
            return [
                'usuari_id' => $usuariId,
                'xp_total' => 0,
                'ratxa_actual' => 0,
                'ratxa_maxima' => 0,
            ];
        }

        // B. Recuperar ratxa (si existeix)
        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();

        if ($ratxa === null) {
            $ratxaActual = 0;
            $ratxaMaxima = 0;
        } else {
            if (isset($ratxa->ratxa_actual)) {
                $ratxaActual = (int) $ratxa->ratxa_actual;
            } else {
                $ratxaActual = 0;
            }

            if (isset($ratxa->ratxa_maxima)) {
                $ratxaMaxima = (int) $ratxa->ratxa_maxima;
            } else {
                $ratxaMaxima = 0;
            }
        }

        // C. Retornar valors normalitzats
        return [
            'usuari_id' => $usuariId,
            'xp_total' => (int) $usuari->xp_total,
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
        ];
    }
}
