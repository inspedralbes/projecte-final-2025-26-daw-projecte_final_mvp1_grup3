<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminReportResource;
use App\Models\Report;
use Illuminate\Http\JsonResponse;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador de Reports per a l'administrador.
 * Gestiona la visualització de denúncies i contingut reportat.
 */
class AdminReportController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Retorna la llista de reports paginada.
     * Pas A: Recuperar els reports amb l'usuari que ha reportat.
     * Pas B: Transformar les dades al format que espera el frontend.
     */
    public function index(int $page = 1, int $perPage = 20): JsonResponse
    {
        // A. Recuperació de dades
        // Utilitzem Eloquent per obtenir els reports amb la relació de l'usuari
        $paginator = Report::with('usuari:id,nom')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = AdminReportResource::collection($paginator->items())->resolve(request());
        $dataArray = $items['data'] ?? $items;

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $dataArray,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                ],
            ],
        ]);
    }
}
