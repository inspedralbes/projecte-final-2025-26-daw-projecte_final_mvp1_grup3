<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminHabitResource;
use App\Models\Habit;
use Illuminate\Http\JsonResponse;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador d'hàbits per l'admin.
 * READ via Laravel; CUD via Socket → Redis → Laravel.
 */
class AdminHabitController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista hàbits paginats.
     */
    public function index(int $page = 1, int $perPage = 20): JsonResponse
    {
        if ($perPage < 1) {
            $perPage = 20;
        }
        if ($page < 1) {
            $page = 1;
        }

        $paginator = Habit::with(['usuari:id,nom,email', 'plantilla:id,titol,categoria'])
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = AdminHabitResource::collection($paginator->items())->resolve(request());
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
