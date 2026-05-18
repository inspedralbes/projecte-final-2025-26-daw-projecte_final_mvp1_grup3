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
    public function __construct(
        private MissionService $missionService
    ) {}

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
                'nivell' => 1,
                'xp_actual_nivel' => 0,
                'xp_objetivo_nivel' => 1000,
                'ratxa_actual' => 0,
                'ratxa_maxima' => 0,
                'monedes' => 0,
                'missio_diaria' => null,
                'missio_completada' => false,
                'streak_incremented' => false,
            ];
        }

        // B. Reset diari i assignació de nova missió si cal
        $this->comprovarResetIAssignarMissio($usuari);

        // B2. Actualitzar ratxa per entrar a l'app
        $streakIncremented = $this->actualitzarRatxa($usuariId);

        // C. Recuperar ratxa (reload usuari per si s'ha actualitzat)
        $usuari = $usuari->fresh();
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
        $missioProgres = 0;
        $missioObjectiu = 1;
        $progresMissio = $this->missionService->obtenirProgresMissio($usuariId);
        if ($progresMissio !== null) {
            $missioProgres = (int) $progresMissio['progres'];
            $missioObjectiu = (int) $progresMissio['objectiu'];
        }
        if (isset($usuari->monedes)) {
            $monedes = (int) $usuari->monedes;
        } else {
            $monedes = 0;
        }

        // E. Estat de la ruleta diària
        $ruletaUltimaTirada = $usuari->ruleta_ultima_tirada;
        $potTirarRuleta = true;
        if ($ruletaUltimaTirada !== null) {
            $dataTirada = Carbon::parse($ruletaUltimaTirada)->startOfDay();
            if ($dataTirada->isSameDay(Carbon::today())) {
                $potTirarRuleta = false;
            }
        }

        // F. Retornar valors normalitzats
        return [
            'usuari_id' => $usuariId,
            'xp_total' => (int) $usuari->xp_total,
            'nivell' => isset($usuari->nivell) ? (int) $usuari->nivell : 1,
            'xp_actual_nivel' => isset($usuari->xp_actual_nivel) ? (int) $usuari->xp_actual_nivel : 0,
            'xp_objetivo_nivel' => isset($usuari->xp_objetivo_nivel) ? (int) $usuari->xp_objetivo_nivel : 1000,
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
            'monedes' => $monedes,
            'can_spin_roulette' => $potTirarRuleta,
            'ruleta_ultima_tirada' => $ruletaUltimaTirada,
            'missio_diaria' => $missioDiaria,
            'missio_completada' => $missioCompletada,
            'missio_progres' => $missioProgres,
            'missio_objectiu' => $missioObjectiu,
            'monstre_tipus' => $usuari->monstre_tipus,
            'streak_incremented' => $streakIncremented,
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

    /**
     * Actualitza la ratxa de l'usuari. 
     * Si és un dia nou consecutiu, incrementa la ratxa.
     * Si ha passat més d'un dia, la reseteja a 1 (comença de nou).
     * Si és el mateix dia, no fa res.
     *
     * @param int $usuariId
     */
    public function actualitzarRatxa(int $usuariId): bool
    {
        $timezone = config('app.timezone', 'Europe/Madrid');
        $avui = Carbon::now($timezone)->startOfDay();

        $ratxa = Ratxa::firstOrCreate(
            ['usuari_id' => $usuariId],
            [
                'ratxa_actual' => 0,
                'ratxa_maxima' => 0,
                'ultima_data' => null,
            ]
        );

        // A. Si hi ha data prèvia, parsejar-la
        $ultimaData = null;
        if ($ratxa->ultima_data !== null) {
            $ultimaData = Carbon::parse($ratxa->ultima_data, $timezone)->startOfDay();
        }

        $ratxaActual = (int) $ratxa->ratxa_actual;
        $ratxaMaxima = (int) $ratxa->ratxa_maxima;

        // B. Si és el mateix dia, no modifiquem la ratxa
        if ($ultimaData !== null && $ultimaData->isSameDay($avui)) {
            return false;
        }

        // C. Si és el dia següent, incrementem
        if ($ultimaData !== null && $avui->diffInDays($ultimaData) === 1) {
            $ratxaActual++;
        } else {
            // Gap o primera vegada: racha 1
            $ratxaActual = 1;
        }

        $ratxaMaxima = max($ratxaMaxima, $ratxaActual);

        $ratxa->update([
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
            'ultima_data' => $avui->toDateString(),
        ]);

        return true;
    }
}
