<?php

namespace App\Http\Controllers;

//================================ NAMESPACES / IMPORTS ============

use App\Services\CalendarService;
use Illuminate\Http\JsonResponse;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador del calendari (Arxiu d'Aventures).
 * Endpoints de lectura de snapshots.
 */
class CalendarController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna el snapshot complet d'un dia.
     */
    public function showDay(int $usuariId, string $data, CalendarService $calendarService): JsonResponse
    {
        $snapshot = $calendarService->getSnapshotDia($usuariId, $data);

        if ($snapshot === null) {
            return response()->json(['message' => 'No snapshot found for this date'], 404);
        }

        return response()->json([
            'mascota_json' => $snapshot->mascota_json,
            'habits_json' => $snapshot->habits_json,
            'economia_json' => $snapshot->economia_json,
            'data' => $snapshot->data->format('Y-m-d'),
        ]);
    }

    /**
     * Retorna el resum mensual per al grid.
     */
    public function showMonth(int $usuariId, int $year, int $month, CalendarService $calendarService): JsonResponse
    {
        $resultat = $calendarService->getResumMensual($usuariId, $year, $month);
        return response()->json($resultat);
    }
}
