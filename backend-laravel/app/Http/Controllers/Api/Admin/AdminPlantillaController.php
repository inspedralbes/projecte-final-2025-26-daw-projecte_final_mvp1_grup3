<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminPlantillaResource;
use App\Models\Plantilla;
use Illuminate\Http\JsonResponse;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador de plantilles per l'admin.
 * READ via Laravel; CUD via Socket → Redis → Laravel.
 */
class AdminPlantillaController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista plantilles paginades.
     */
    public function index(int $page = 1, int $perPage = 20): JsonResponse
    {
        if ($perPage < 1) {
            $perPage = 20;
        }
        if ($page < 1) {
            $page = 1;
        }

        $paginator = Plantilla::with('creador:id,nom,email')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = AdminPlantillaResource::collection($paginator->items())->resolve(request());
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
