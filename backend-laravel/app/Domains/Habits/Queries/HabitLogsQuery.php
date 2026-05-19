<?php

declare(strict_types=1);

namespace App\Domains\Habits\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Models\UsuariHabit;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Consulta logs històrics agregats per dia i hàbit.
 */
class HabitLogsQuery
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function executar(int $usuariId): array
    {
        $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');
        $habitIds = Habit::where('usuari_id', $usuariId)
            ->orWhereIn('id', $habitIdsAssignats)
            ->pluck('id')
            ->toArray();

        if (empty($habitIds)) {
            return [];
        }

        $files = DB::table('registre_activitat as ra')
            ->join('habits as h', 'ra.habit_id', '=', 'h.id')
            ->whereIn('h.id', $habitIds)
            ->selectRaw('DATE(ra.data) as dia')
            ->selectRaw('h.id as habit_id, h.titol, h.unitat, h.objectiu_vegades, h.dificultat, h.icona, h.color')
            ->selectRaw('COALESCE(SUM(ra.valor), 0) as progreso_diario')
            ->selectRaw('MAX(CASE WHEN ra.acabado = true THEN 1 ELSE 0 END) as completado')
            ->selectRaw('COALESCE(SUM(CASE WHEN ra.acabado = true THEN ra.xp_guanyada ELSE 0 END), 0) as xp_ganada')
            ->groupBy('dia', 'h.id', 'h.titol', 'h.unitat', 'h.objectiu_vegades', 'h.dificultat', 'h.icona', 'h.color')
            ->orderBy('dia', 'desc')
            ->get();

        $resultat = [];
        foreach ($files as $fila) {
            $monedes = 2;
            $dificultat = strtolower((string) $fila->dificultat);
            if ((int) $fila->completado === 1) {
                if ($dificultat === 'facil') {
                    $monedes = 2;
                } elseif ($dificultat === 'media') {
                    $monedes = 5;
                } elseif ($dificultat === 'dificil') {
                    $monedes = 10;
                }
            }
            $resultat[] = [
                'dia' => $fila->dia,
                'habit_id' => (int) $fila->habit_id,
                'titol' => $fila->titol,
                'unitat' => $fila->unitat,
                'icona' => $fila->icona,
                'color' => $fila->color,
                'objectiu_vegades' => (int) $fila->objectiu_vegades,
                'progreso_diario' => (int) $fila->progreso_diario,
                'completado' => ((int) $fila->completado === 1),
                'xp_ganada' => (int) $fila->xp_ganada,
                'monedes_ganadas' => $monedes,
            ];
        }

        return $resultat;
    }
}
