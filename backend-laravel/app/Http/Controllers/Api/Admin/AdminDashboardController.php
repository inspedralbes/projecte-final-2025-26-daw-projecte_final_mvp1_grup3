<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Habit;
use App\Models\Plantilla;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador del Dashboard de l'admin.
 * Retorna mètriques ràpides, top 5 plantilles/hàbits i últims logs.
 */
class AdminDashboardController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna les mètriques i resum del dashboard.
     * Pas A: Comptar usuaris totals, prohibits, plantilles públiques.
     * Pas B: Obtenir top 5 plantilles i hàbits més usats.
     * Pas C: Obtenir últims 10 logs.
     */
    public function index(): JsonResponse
    {
        // A. Mètriques ràpides
        $usuarisTotals = User::count();

        // Mètrica de prohibits (resilient si la columna encara no existeix en la DB actual)
        $prohibits = 0;
        // A1. Intentar comptar prohibits (si la columna existeix)
        try {
            $prohibits = User::where('prohibit', true)->count();
        } catch (\Exception $e) {
            // Si la columna no existeix, simplement retornem 0 per no trencar el dashboard
            $prohibits = 0;
        }
        $plantillesPubliques = Plantilla::where('es_publica', true)->count();

        // Connectats ara: MVP1 sense Node integrat, retornem 0
        $connectatsAra = 0;

        // B. Top 5 plantilles més usades (per nombre d'hàbits creats des de la plantilla)
        $topPlantillesRaw = Habit::select('plantilla_id', DB::raw('COUNT(*) as vegades_usada'))
            ->whereNotNull('plantilla_id')
            ->groupBy('plantilla_id')
            ->orderByDesc('vegades_usada')
            ->limit(5)
            ->with('plantilla:id,titol,categoria')
            ->get();

        $topPlantilles = [];
        // B1. Recórrer plantilles top i normalitzar format
        foreach ($topPlantillesRaw as $item) {
            // B1.1. Només afegir si la plantilla existeix
            if ($item->plantilla !== null) {
                $topPlantilles[] = [
                    'id' => $item->plantilla_id,
                    'nom' => $item->plantilla->titol,
                    'categoria' => $item->plantilla->categoria,
                    'vegades_usada' => $item->vegades_usada,
                ];
            }
        }

        // Top 5 hàbits més completats (per REGISTRE_ACTIVITAT)
        $topHabits = DB::table('registre_activitat')
            ->select('habit_id', DB::raw('COUNT(*) as completions'))
            ->where('acabado', true)
            ->groupBy('habit_id')
            ->orderByDesc('completions')
            ->limit(5)
            ->get();

        $habitsIds = $topHabits->pluck('habit_id')->toArray();
        $habits = Habit::whereIn('id', $habitsIds)->get()->keyBy('id');

        $top5Habits = [];
        // B2. Recórrer hàbits top i normalitzar format
        foreach ($topHabits as $item) {
            $habit = $habits->get($item->habit_id);
            $nomHabit = 'Desconegut';
            // B2.1. Si existeix el hàbit, usar el seu títol
            if ($habit !== null) {
                $nomHabit = $habit->titol;
            }
            $top5Habits[] = [
                'id' => $item->habit_id,
                'nom' => $nomHabit,
                'completions' => $item->completions,
            ];
        }

        // C. Últims 10 logs
        $ultimsLogsRaw = AdminLog::with('administrador:id,nom')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $ultimsLogs = [];
        // C1. Recórrer logs i normalitzar format
        foreach ($ultimsLogsRaw as $log) {
            $createdAtStr = null;
            // C1.1. Si hi ha created_at, convertir-lo
            if ($log->created_at !== null) {
                $createdAtStr = $log->created_at->toIso8601String();
            }
            $adminNom = 'Admin';
            // C1.2. Si hi ha administrador i nom, usar-lo
            if ($log->administrador !== null && $log->administrador->nom !== null) {
                $adminNom = $log->administrador->nom;
            }
            $ultimsLogs[] = [
                'id' => $log->id,
                'created_at' => $createdAtStr,
                'accio' => $log->accio,
                'administrador_nom' => $adminNom,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'totalUsuaris' => $usuarisTotals,
                'connectats' => $connectatsAra,
                'prohibits' => $prohibits,
                'logrosActius' => $plantillesPubliques, // En el frontend s'usava per plantilles o similar
                'totalHabits' => Habit::count(),
                'top_5_plantilles' => $topPlantilles,
                'top_5_habits' => $top5Habits,
                'ultims_10_logs' => $ultimsLogs,
            ]
        ]);
    }
}
