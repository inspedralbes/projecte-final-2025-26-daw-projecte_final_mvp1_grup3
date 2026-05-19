<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\UserReport;
use App\Services\AdminReportBroadcastService;
use Illuminate\Http\JsonResponse;

class AdminReportController extends Controller
{
    public function __construct(
        private AdminReportBroadcastService $reportBroadcast
    ) {}

    /**
     * Retorna la llista de reports de les dues taules paginada.
     */
    public function index(int $page = 1, int $perPage = 20): JsonResponse
    {
        // 1. Recuperar reports estàndards
        $reports = Report::with('usuari:id,nom')
            ->orderByDesc('created_at')
            ->get();

        // 2. Recuperar reports d'usuaris específics
        $userReports = UserReport::with(['usuari:id,nom', 'reportat:id,nom'])
            ->orderByDesc('created_at')
            ->get();

        $merged = [];

        foreach ($reports as $r) {
            $createdAt = $r->created_at ? \Carbon\Carbon::parse($r->created_at) : null;
            $merged[] = [
                'id' => $r->id,
                'table' => 'reports',
                'usuari' => $r->usuari ? $r->usuari->nom : 'Sistema',
                'tipus' => $r->tipus ?? '',
                'contingut' => $r->contingut ?? null,
                'post_id' => $r->post_id ?? null,
                'reported_user_nom' => null,
                'created_at' => $createdAt ? $createdAt->toIso8601String() : null,
                'data' => $createdAt ? $createdAt->diffForHumans() : 'Fa poc',
            ];
        }

        foreach ($userReports as $ur) {
            $createdAt = $ur->created_at ? \Carbon\Carbon::parse($ur->created_at) : null;
            $merged[] = [
                'id' => $ur->id,
                'table' => 'reports_usuari',
                'usuari' => $ur->usuari ? $ur->usuari->nom : 'Sistema',
                'tipus' => 'user',
                'contingut' => '[' . $ur->motiu . '] ' . ($ur->detalls ?: 'Sense detalls'),
                'post_id' => $ur->reportat_id,
                'reported_user_nom' => $ur->reportat ? $ur->reportat->nom : 'Desconegut',
                'created_at' => $createdAt ? $createdAt->toIso8601String() : null,
                'data' => $createdAt ? $createdAt->diffForHumans() : 'Fa poc',
            ];
        }

        // Ordenar per data de creació descendent
        usort($merged, function($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        // Paginació manual
        $total = count($merged);
        $offset = ($page - 1) * $perPage;
        $sliced = array_slice($merged, $offset, $perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $sliced,
                'meta' => [
                    'current_page' => $page,
                    'total' => $total,
                    'per_page' => $perPage,
                ],
            ],
        ]);
    }

    /**
     * Elimina (o resol) un report de la taula corresponent.
     */
    public function destroy(int $id): JsonResponse
    {
        $table = request()->query('table', 'reports');

        if ($table === 'reports_usuari') {
            $report = UserReport::findOrFail($id);
        } else {
            $report = Report::findOrFail($id);
        }

        $report->delete();

        $this->reportBroadcast->notificarReportEliminat($id, $table);

        return response()->json([
            'success' => true,
            'message' => 'Report eliminat correctament.'
        ]);
    }
}
