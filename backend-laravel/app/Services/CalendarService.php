<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\DailySnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei de consulta del calendari.
 * Retorna snapshots i resums mensuals en format API.
 */
class CalendarService
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna el snapshot complet d'un dia.
     */
    public function getSnapshotDia(int $usuariId, string $data): ?DailySnapshot
    {
        return DailySnapshot::where('usuari_id', $usuariId)
            ->where('data', $data)
            ->first();
    }

    /**
     * Retorna el resum mensual lleuger per al grid.
     */
    public function getResumMensual(int $usuariId, int $year, int $month): array
    {
        $diesMes = Carbon::create($year, $month, 1)->daysInMonth;
        $snapshots = DailySnapshot::where('usuari_id', $usuariId)
            ->whereYear('data', $year)
            ->whereMonth('data', $month)
            ->get()
            ->keyBy(function ($snapshot) {
                return (int) $snapshot->data->format('j');
            });

        $categoriesColors = DB::table('categories')
            ->pluck('color', 'id')
            ->toArray();

        $resultat = [];
        for ($dia = 1; $dia <= $diesMes; $dia = $dia + 1) {
            if (isset($snapshots[$dia])) {
                $snapshot = $snapshots[$dia];
                $habitsJson = $snapshot->habits_json;
                $colorsUnics = [];

                if (is_array($habitsJson)) {
                    foreach ($habitsJson as $habit) {
                        if (isset($habit['acabado']) && $habit['acabado'] === true && isset($habit['categoria_id'])) {
                            $catId = $habit['categoria_id'];
                            if (isset($categoriesColors[$catId]) && !in_array($categoriesColors[$catId], $colorsUnics)) {
                                $colorsUnics[] = $categoriesColors[$catId];
                            }
                        }
                    }
                }

                $element = [
                    'day' => $dia,
                    'has_snapshot' => true,
                    'category_colors' => $colorsUnics,
                ];
            } else {
                $element = [
                    'day' => $dia,
                    'has_snapshot' => false,
                    'category_colors' => [],
                ];
            }
            $resultat[] = $element;
        }

        return $resultat;
    }
}
