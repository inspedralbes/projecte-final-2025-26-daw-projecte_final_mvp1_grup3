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
        $prohibits = User::where('prohibit', true)->count();
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
        foreach ($topPlantillesRaw as $item) {
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
        foreach ($topHabits as $item) {
            $habit = $habits->get($item->habit_id);
            $nomHabit = 'Desconegut';
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
        foreach ($ultimsLogsRaw as $log) {
            $createdAtStr = null;
            if ($log->created_at !== null) {
                $createdAtStr = $log->created_at->toIso8601String();
            }
            $adminNom = 'Admin';
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
            'usuaris_totals' => $usuarisTotals,
            'connectats_ara' => $connectatsAra,
            'prohibits' => $prohibits,
            'plantilles_publiques' => $plantillesPubliques,
            'top_5_plantilles' => $topPlantilles,
            'top_5_habits' => $top5Habits,
            'ultims_10_logs' => $ultimsLogs,
        ]);
    }
}
