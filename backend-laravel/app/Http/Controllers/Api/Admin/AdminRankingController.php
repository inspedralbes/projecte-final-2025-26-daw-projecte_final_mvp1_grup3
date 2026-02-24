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
        if ($periodo === 'setmana') {
            $desde = now()->subWeek();
        }
        if ($periodo === 'mes') {
            $desde = now()->subMonth();
        }

        // Ranking plantilles (per nombre d'hàbits creats des de la plantilla)
        $queryPlantilles = DB::table('habits')
            ->select('plantilla_id', DB::raw('COUNT(*) as vegades_usada'))
            ->whereNotNull('plantilla_id');

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
        foreach ($plantillesRaw as $item) {
            $plantilla = $plantilles->get($item->plantilla_id);
            $percent = 0;
            if ($totalPlantilles > 0) {
                $percent = round(($item->vegades_usada / $totalPlantilles) * 100, 1);
            }
            $nom = 'Desconegut';
            $categoria = '';
            if ($plantilla !== null) {
                if ($plantilla->titol !== null) {
                    $nom = $plantilla->titol;
                }
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
        foreach ($habitsRaw as $item) {
            $habit = $habits->get($item->habit_id);
            $percent = 0;
            if ($totalCompletions > 0) {
                $percent = round(($item->completions / $totalCompletions) * 100, 1);
            }
            $nom = 'Desconegut';
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
