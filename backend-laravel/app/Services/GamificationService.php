<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\MissioDiaria;
use App\Models\Ratxa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de gamificació.
 * Centralitza la lectura d'XP, ratxes, monedes i missió diària de l'usuari.
 */
class GamificationService
{
    /**
     * Nombre de missions disponibles (IDs 1-15).
     */
    private const NUM_MISSIOS = 15;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Obté l'estat de gamificació d'un usuari.
     *
     * A. Recuperar usuari i validar.
     * B. Comprovar reset diari i assignar nova missió si cal.
     * C. Recuperar ratxa.
     * D. Retornar valors normalitzats (xp, ratxa, monedes, missio_diaria, missio_completada).
     *
     * @param  int  $usuariId
     * @return array<string, mixed>
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
                'monedes' => 0,
                'missio_diaria' => null,
                'missio_completada' => false,
            ];
        }

        // B. Reset diari i assignació de nova missió si cal
        $this->comprovarResetIAssignarMissio($usuari);

        // C. Recuperar ratxa (reload usuari per si s'ha actualitzat)
        $usuari = $usuari->fresh();
        $ratxa = Ratxa::where('usuari_id', $usuariId)->first();

        if ($ratxa === null) {
            $ratxaActual = 0;
            $ratxaMaxima = 0;
        } else {
            $ratxaActual = isset($ratxa->ratxa_actual) ? (int) $ratxa->ratxa_actual : 0;
            $ratxaMaxima = isset($ratxa->ratxa_maxima) ? (int) $ratxa->ratxa_maxima : 0;
        }

        // D. Missió diària
        $missioDiaria = null;
        if ($usuari->missio_diaria_id !== null) {
            $missio = MissioDiaria::find($usuari->missio_diaria_id);
            if ($missio !== null) {
                $missioDiaria = [
                    'id' => $missio->id,
                    'titol' => $missio->titol,
                ];
            }
        }

        $missioCompletada = (bool) $usuari->missio_completada;
        $monedes = isset($usuari->monedes) ? (int) $usuari->monedes : 0;

        // E. Retornar valors normalitzats
        return [
            'usuari_id' => $usuariId,
            'xp_total' => (int) $usuari->xp_total,
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
            'monedes' => $monedes,
            'missio_diaria' => $missioDiaria,
            'missio_completada' => $missioCompletada,
        ];
    }

    /**
     * Comprova si cal reset diari i assigna nova missió (excloent la del dia anterior).
     *
     * @param  User  $usuari
     */
    private function comprovarResetIAssignarMissio(User $usuari): void
    {
        $avui = Carbon::today();
        $ultimReset = $usuari->ultim_reset_missio;

        // A. Si ultim_reset és avui, no cal fer res
        if ($ultimReset !== null) {
            $dataReset = Carbon::parse($ultimReset)->startOfDay();
            if ($dataReset->isSameDay($avui)) {
                return;
            }
        }

        // B. Nou dia: reset missio_completada i assignar nova missió
        $missioAnteriorId = $usuari->missio_diaria_id;

        // B1. Construir llista de candidats (1-15 excepte la missió anterior)
        $candidats = [];
        for ($i = 1; $i <= self::NUM_MISSIOS; $i++) {
            if ($missioAnteriorId === null || $i !== (int) $missioAnteriorId) {
                $candidats[] = $i;
            }
        }

        // B2. Si no hi ha candidats (cas límit), usar totes
        if (empty($candidats)) {
            for ($i = 1; $i <= self::NUM_MISSIOS; $i++) {
                $candidats[] = $i;
            }
        }

        // B3. Escollir aleatori
        $indexAleatori = array_rand($candidats);
        $novaMissioId = $candidats[$indexAleatori];

        // B4. Actualitzar usuari
        DB::table('usuaris')->where('id', $usuari->id)->update([
            'missio_completada' => false,
            'missio_diaria_id' => $novaMissioId,
            'ultim_reset_missio' => $avui->toDateString(),
        ]);
    }
}
