<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminLogroResource;
use App\Models\LogroMedalla;
use Illuminate\Http\JsonResponse;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador de logros per l'admin.
 * READ via Laravel; CUD via Socket → Redis → Laravel.
 */
class AdminLogroController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista logros paginats.
     */
    public function index(int $page = 1, int $perPage = 20): JsonResponse
    {
        if ($perPage < 1) {
            $perPage = 20;
        }
        if ($page < 1) {
            $page = 1;
        }

        $paginator = LogroMedalla::orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = AdminLogroResource::collection($paginator->items())->resolve(request());
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
