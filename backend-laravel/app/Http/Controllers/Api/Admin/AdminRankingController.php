<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador de rankings per plantilles i hàbits.
 * periodo: setmana, mes, total.
 */
class AdminRankingController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna rankings de plantilles i hàbits més usats.
     */
    public function index(string $periodo = 'total'): JsonResponse
    {
        $desde = null;
        // A. Si el període és setmana, calcular data d'inici
        if ($periodo === 'setmana') {
            $desde = now()->subWeek();
        }
        // B. Si el període és mes, calcular data d'inici
        if ($periodo === 'mes') {
            $desde = now()->subMonth();
        }

        // Ranking plantilles (per nombre d'hàbits creats des de la plantilla)
        $queryPlantilles = DB::table('habits')
            ->select('plantilla_id', DB::raw('COUNT(*) as vegades_usada'))
            ->whereNotNull('plantilla_id');

        // C. Si hi ha filtre temporal, aplicar-lo (placeholder)
        if ($desde !== null) {
            $queryPlantilles->whereRaw('1=1');
        }

        $plantillesRaw = $queryPlantilles->groupBy('plantilla_id')
            ->orderByDesc('vegades_usada')
            ->limit(20)
            ->get();

        $totalPlantilles = DB::table('habits')->whereNotNull('plantilla_id')->count();
        $plantilles = DB::table('plantilles')->whereIn('id', $plantillesRaw->pluck('plantilla_id'))->get()->keyBy('id');

        $rankingPlantilles = [];
        $indexP = 0;
        // D. Recórrer plantilles i construir ranking
        foreach ($plantillesRaw as $item) {
            $plantilla = $plantilles->get($item->plantilla_id);
            $percent = 0;
            // D1. Si hi ha total, calcular percentatge
            if ($totalPlantilles > 0) {
                $percent = round(($item->vegades_usada / $totalPlantilles) * 100, 1);
            }
            $nom = 'Desconegut';
            $categoria = '';
            // D2. Si la plantilla existeix, recuperar nom i categoria
            if ($plantilla !== null) {
                // D2.1. Si hi ha títol, usar-lo
                if ($plantilla->titol !== null) {
                    $nom = $plantilla->titol;
                }
                // D2.2. Si hi ha categoria, usar-la
                if ($plantilla->categoria !== null) {
                    $categoria = $plantilla->categoria;
                }
            }
            $rankingPlantilles[] = [
                'posicio' => $indexP + 1,
                'nom' => $nom,
                'categoria' => $categoria,
                'vegades_usada' => $item->vegades_usada,
                'percent' => $percent,
            ];
            $indexP++;
        }

        // Ranking hàbits (per nombre de completions)
        $queryHabits = DB::table('registre_activitat')
            ->select('habit_id', DB::raw('COUNT(*) as completions'))
            ->where('acabado', true);

        // E. Si hi ha filtre temporal, aplicar-lo a hàbits
        if ($desde !== null) {
            $queryHabits->where('data', '>=', $desde);
        }

        $habitsRaw = $queryHabits->groupBy('habit_id')
            ->orderByDesc('completions')
            ->limit(20)
            ->get();

        $totalCompletions = DB::table('registre_activitat')->where('acabado', true)->count();
        $habits = DB::table('habits')->whereIn('id', $habitsRaw->pluck('habit_id'))->get()->keyBy('id');

        $rankingHabits = [];
        $indexH = 0;
        // F. Recórrer hàbits i construir ranking
        foreach ($habitsRaw as $item) {
            $habit = $habits->get($item->habit_id);
            $percent = 0;
            // F1. Si hi ha total, calcular percentatge
            if ($totalCompletions > 0) {
                $percent = round(($item->completions / $totalCompletions) * 100, 1);
            }
            $nom = 'Desconegut';
            // F2. Si existeix el hàbit i té títol, usar-lo
            if ($habit !== null && $habit->titol !== null) {
                $nom = $habit->titol;
            }
            $rankingHabits[] = [
                'posicio' => $indexH + 1,
                'nom' => $nom,
                'completions' => $item->completions,
                'percent' => $percent,
            ];
            $indexH++;
        }

        return response()->json([
            'periodo' => $periodo,
            'ranking_plantilles' => $rankingPlantilles,
            'ranking_habits' => $rankingHabits,
        ]);
    }
}
