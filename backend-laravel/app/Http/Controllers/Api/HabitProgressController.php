<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\RegistreActivitat;
use App\Models\UsuariHabit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controlador per obtenir progrés diari i logs d'hàbits.
 */
class HabitProgressController extends Controller
{
    /**
     * Retorna el progrés d'avui per a tots els hàbits de l'usuari.
     */
    public function today(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');
        $diaIndex = (int) now()->dayOfWeekIso; // 1..7
        $habits = Habit::where('usuari_id', $usuariId)
            ->orWhereIn('id', $habitIdsAssignats)
            ->where(function ($q) use ($diaIndex) {
                $q->whereNull('dies_setmana')
                    ->orWhereRaw('dies_setmana[' . $diaIndex . '] = true');
            })
            ->get(['id', 'objectiu_vegades', 'titol', 'unitat', 'dificultat', 'icona', 'color']);

        $habitIds = $habits->pluck('id')->toArray();
        $avui = Carbon::today();

        $progres = DB::table('registre_activitat')
            ->select('habit_id', DB::raw('COALESCE(SUM(valor), 0) as progress'))
            ->whereIn('habit_id', $habitIds)
            ->whereDate('data', $avui)
            ->groupBy('habit_id')
            ->get()
            ->keyBy('habit_id');

        $completats = RegistreActivitat::whereIn('habit_id', $habitIds)
            ->whereDate('data', $avui)
            ->where('acabado', true)
            ->pluck('habit_id')
            ->toArray();

        $resultat = [];
        foreach ($habits as $habit) {
            $progress = 0;
            if (isset($progres[$habit->id])) {
                $progress = (int) $progres[$habit->id]->progress;
            }
            $resultat[] = [
                'habit_id' => $habit->id,
                'progress' => $progress,
                'completed_today' => in_array($habit->id, $completats),
                'objectiu_vegades' => (int) $habit->objectiu_vegades,
                'titol' => $habit->titol,
                'unitat' => $habit->unitat,
                'icona' => $habit->icona,
                'color' => $habit->color,
            ];
        }

        return response()->json($resultat);
    }

    /**
     * Retorna logs diaris agregats per hàbit.
     */
    public function logs(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');
        $habitIds = Habit::where('usuari_id', $usuariId)
            ->orWhereIn('id', $habitIdsAssignats)
            ->pluck('id')
            ->toArray();

        if (empty($habitIds)) {
            return response()->json([]);
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
            $monedes = 0;
            $dificultat = strtolower((string) $fila->dificultat);
            if ((int) $fila->completado === 1) {
                if ($dificultat === 'facil') {
                    $monedes = 2;
                } elseif ($dificultat === 'media') {
                    $monedes = 5;
                } elseif ($dificultat === 'dificil') {
                    $monedes = 10;
                } else {
                    $monedes = 2;
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

        return response()->json($resultat);
    }
}
