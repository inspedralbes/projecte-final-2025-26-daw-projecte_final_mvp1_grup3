<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Models\MissioDiaria;
use App\Models\Ratxa;
use App\Models\RegistreActivitat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de missions diàries.
 * Comprova si l'usuari ha completat la missió assignada després de registrar un hàbit.
 */
class MissionService
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Comprova si la missió diària de l'usuari s'ha completat després de completar un hàbit.
     * Si es compleix: actualitza missio_completada, suma 10 monedes i 20 XP, retorna dades per xp_update.
     *
     * @param  int  $userId
     * @param  int  $habitId
     * @param  Carbon  $timestamp
     * @return array{completada: bool, xp_update?: array}|null
     */
    public function comprovarMissioCompletada(int $userId, int $habitId, Carbon $timestamp): ?array
    {
        // A. Obtenir usuari i missió assignada
        $usuari = User::find($userId);
        if ($usuari === null || $usuari->missio_diaria_id === null) {
            return null;
        }

        // A1. Si ja està completada, no fer res
        if ($usuari->missio_completada === true) {
            return null;
        }

        $missio = MissioDiaria::find($usuari->missio_diaria_id);
        if ($missio === null) {
            return null;
        }

        // B. Comprovar segons tipus_comprovacio
        $compleix = $this->compleixMissio($missio, $userId, $habitId, $timestamp);

        if (! $compleix) {
            return null;
        }

        // C. Actualitzar usuari: missio_completada, +10 monedes, +20 XP
        DB::transaction(function () use ($usuari) {
            User::where('id', $usuari->id)->update([
                'missio_completada' => true,
                'monedes' => $usuari->monedes + 10,
                'xp_total' => $usuari->xp_total + 20,
            ]);
        });

        // D. Recuperar valors actualitzats i retornar xp_update
        $usuariRefresh = User::find($userId);
        $ratxa = Ratxa::where('usuari_id', $userId)->first();

        $ratxaActual = 0;
        $ratxaMaxima = 0;
        if ($ratxa !== null) {
            $ratxaActual = (int) $ratxa->ratxa_actual;
            $ratxaMaxima = (int) $ratxa->ratxa_maxima;
        }

        $objectiu = $this->obtenirObjectiuMissio(MissioDiaria::find($usuari->missio_diaria_id));

        return [
            'completada' => true,
            'missio_objectiu' => $objectiu,
            'xp_update' => [
                'xp_total' => (int) $usuariRefresh->xp_total,
                'ratxa_actual' => $ratxaActual,
                'ratxa_maxima' => $ratxaMaxima,
                'monedes' => (int) $usuariRefresh->monedes,
            ],
        ];
    }

    /**
     * Comprova si la missió es compleix segons el seu tipus.
     *
     * @param  MissioDiaria  $missio
     * @param  int  $userId
     * @param  int  $habitId
     * @param  Carbon  $timestamp
     */
    private function compleixMissio(MissioDiaria $missio, int $userId, int $habitId, Carbon $timestamp): bool
    {
        $tipus = $missio->tipus_comprovacio ?? '';
        $parametres = $missio->parametres ?? [];
        $avui = $timestamp->copy()->startOfDay();

        // A. hab_1_qualsevol: almenys 1 registre avui
        if ($tipus === 'hab_1_qualsevol') {
            return $this->comptarRegistresAvui($userId, $avui) >= 1;
        }

        // B. hab_fins_hora: almenys 1 registre avui abans de l'hora
        if ($tipus === 'hab_fins_hora') {
            $hora = isset($parametres['hora']) ? (int) $parametres['hora'] : 24;
            return $this->comptarRegistresAvuiAbansHora($userId, $avui, $hora) >= 1;
        }

        // C. hab_n_qualsevol: n o més registres avui
        if ($tipus === 'hab_n_qualsevol') {
            $n = isset($parametres['n']) ? (int) $parametres['n'] : 1;
            return $this->comptarRegistresAvui($userId, $avui) >= $n;
        }

        // D. hab_dificultat: existeix registre avui amb dificultat X
        if ($tipus === 'hab_dificultat') {
            $dificultat = $parametres['dificultat'] ?? '';
            return $this->existeixRegistreAvuiAmbDificultat($userId, $avui, $dificultat);
        }

        // E. hab_categoria: existeix registre avui amb categoria_id X
        if ($tipus === 'hab_categoria') {
            $categoriaId = isset($parametres['categoria_id']) ? (int) $parametres['categoria_id'] : 0;
            return $this->existeixRegistreAvuiAmbCategoria($userId, $avui, $categoriaId);
        }

        // F. hab_primer_del_dia: aquest registre és el primer del dia
        if ($tipus === 'hab_primer_del_dia') {
            return $this->comptarRegistresAvui($userId, $avui) === 1;
        }

        // G. hab_dificultat_multi: existeix registre avui amb dificultat a la llista
        if ($tipus === 'hab_dificultat_multi') {
            $dificultats = $parametres['dificultats'] ?? [];
            if (! is_array($dificultats)) {
                return false;
            }
            return $this->existeixRegistreAvuiAmbDificultats($userId, $avui, $dificultats);
        }

        return false;
    }

    /**
     * Obté el progrés actual de la missió diària de l'usuari (per mostrar X/N a la UI).
     *
     * @return array{progres: int, objectiu: int}|null
     */
    public function obtenirProgresMissio(int $userId): ?array
    {
        $usuari = User::find($userId);
        if ($usuari === null || $usuari->missio_diaria_id === null) {
            return null;
        }

        if ($usuari->missio_completada === true) {
            $missio = MissioDiaria::find($usuari->missio_diaria_id);
            $objectiu = $this->obtenirObjectiuMissio($missio);

            return [
                'progres' => $objectiu,
                'objectiu' => $objectiu,
            ];
        }

        $missio = MissioDiaria::find($usuari->missio_diaria_id);
        if ($missio === null) {
            return null;
        }

        $avui = Carbon::today()->startOfDay();
        $tipus = $missio->tipus_comprovacio ?? '';
        $parametres = is_array($missio->parametres) ? $missio->parametres : [];

        $progres = 0;
        $objectiu = 1;

        if ($tipus === 'hab_1_qualsevol') {
            $progres = min(1, $this->comptarRegistresAvui($userId, $avui));
            $objectiu = 1;
        } elseif ($tipus === 'hab_n_qualsevol') {
            $n = isset($parametres['n']) ? (int) $parametres['n'] : 1;
            $progres = min($n, $this->comptarRegistresAvui($userId, $avui));
            $objectiu = $n;
        } elseif ($tipus === 'hab_fins_hora') {
            $hora = isset($parametres['hora']) ? (int) $parametres['hora'] : 24;
            $progres = $this->comptarRegistresAvuiAbansHora($userId, $avui, $hora) >= 1 ? 1 : 0;
            $objectiu = 1;
        } elseif ($tipus === 'hab_primer_del_dia') {
            $compt = $this->comptarRegistresAvui($userId, $avui);
            $progres = $compt >= 1 ? 1 : 0;
            $objectiu = 1;
        } elseif (in_array($tipus, ['hab_dificultat', 'hab_categoria', 'hab_dificultat_multi'], true)) {
            $progres = $this->compleixMissio($missio, $userId, 0, Carbon::now()) ? 1 : 0;
            $objectiu = 1;
        }

        return [
            'progres' => $progres,
            'objectiu' => $objectiu,
        ];
    }

    /**
     * Retorna l'objectiu numèric d'una missió.
     */
    private function obtenirObjectiuMissio(?MissioDiaria $missio): int
    {
        if ($missio === null) {
            return 1;
        }
        $tipus = $missio->tipus_comprovacio ?? '';
        $parametres = is_array($missio->parametres) ? $missio->parametres : [];
        if ($tipus === 'hab_n_qualsevol') {
            return isset($parametres['n']) ? (int) $parametres['n'] : 1;
        }

        return 1;
    }

    /**
     * Compta registres d'activitat d'avui per l'usuari (via hàbits).
     */
    private function comptarRegistresAvui(int $userId, Carbon $avui): int
    {
        $inici = $avui->copy();
        $fi = $avui->copy()->endOfDay();

        return RegistreActivitat::whereHas('habit', function ($q) use ($userId) {
            $q->where('usuari_id', $userId);
        })->whereBetween('data', [$inici, $fi])->count();
    }

    /**
     * Compta registres d'avui abans d'una hora concreta (0-23).
     */
    private function comptarRegistresAvuiAbansHora(int $userId, Carbon $avui, int $hora): int
    {
        $horaLimit = $avui->copy()->setTime($hora, 0, 0);

        return RegistreActivitat::whereHas('habit', function ($q) use ($userId) {
            $q->where('usuari_id', $userId);
        })->whereDate('data', $avui)->where('data', '<', $horaLimit)->count();
    }

    /**
     * Comprova si existeix un registre d'avui amb la dificultat indicada.
     */
    private function existeixRegistreAvuiAmbDificultat(int $userId, Carbon $avui, string $dificultat): bool
    {
        $inici = $avui->copy();
        $fi = $avui->copy()->endOfDay();
        $dificultatNorm = strtolower(trim($dificultat));

        return RegistreActivitat::whereHas('habit', function ($q) use ($userId, $dificultatNorm) {
            $q->where('usuari_id', $userId)
                ->whereRaw('LOWER(COALESCE(dificultat, \'\')) = ?', [$dificultatNorm]);
        })->whereBetween('data', [$inici, $fi])->exists();
    }

    /**
     * Comprova si existeix un registre d'avui amb la categoria indicada.
     */
    private function existeixRegistreAvuiAmbCategoria(int $userId, Carbon $avui, int $categoriaId): bool
    {
        if ($categoriaId <= 0) {
            return false;
        }

        $inici = $avui->copy();
        $fi = $avui->copy()->endOfDay();

        return RegistreActivitat::whereHas('habit', function ($q) use ($userId, $categoriaId) {
            $q->where('usuari_id', $userId)->where('categoria_id', $categoriaId);
        })->whereBetween('data', [$inici, $fi])->exists();
    }

    /**
     * Comprova si existeix un registre d'avui amb dificultat dins de la llista.
     */
    private function existeixRegistreAvuiAmbDificultats(int $userId, Carbon $avui, array $dificultats): bool
    {
        $inici = $avui->copy();
        $fi = $avui->copy()->endOfDay();
        $dificultatsNorm = array_map(fn ($d) => strtolower(trim((string) $d)), $dificultats);

        return RegistreActivitat::whereHas('habit', function ($q) use ($userId, $dificultatsNorm) {
            $q->where('usuari_id', $userId)
                ->whereRaw('LOWER(COALESCE(dificultat, \'\')) IN (' . implode(',', array_fill(0, count($dificultatsNorm), '?')) . ')', $dificultatsNorm);
        })->whereBetween('data', [$inici, $fi])->exists();
    }
}
