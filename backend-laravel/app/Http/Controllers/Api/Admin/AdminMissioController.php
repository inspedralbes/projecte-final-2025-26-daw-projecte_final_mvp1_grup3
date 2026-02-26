<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\MissioDiaria;
use Illuminate\Http\JsonResponse;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador de missions diàries per l'admin.
 * READ via Laravel; CUD via Socket → Redis → Laravel.
 */
class AdminMissioController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista missions diàries paginades.
     */
    public function index(int $page = 1, int $perPage = 20): JsonResponse
    {
        // A. Normalitzar per_page
        if ($perPage < 1) {
            $perPage = 20;
        }
        // B. Normalitzar page
        if ($page < 1) {
            $page = 1;
        }

        $paginator = MissioDiaria::orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                ],
            ]
        ]);
    }
}
