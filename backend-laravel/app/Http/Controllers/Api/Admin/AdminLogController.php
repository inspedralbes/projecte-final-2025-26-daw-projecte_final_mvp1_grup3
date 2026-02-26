<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\JsonResponse;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador de logs d'administració.
 * Llistat paginat amb filtres per data, administrador, acció i cerca.
 */
class AdminLogController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista logs paginats amb filtres.
     * Paràmetres: page, per_page, data_desde, data_fins, administrador_id, accio, cerca.
     */
    public function index(
        int $page = 1,
        int $perPage = 20,
        $dataDesde = '0',
        $dataFins = '0',
        $administradorId = '0',
        $accio = '-',
        $cerca = '-'
    ): JsonResponse {
        // A. Normalitzar per_page
        if ($perPage < 1) {
            $perPage = 20;
        }
        // B. Normalitzar page
        if ($page < 1) {
            $page = 1;
        }

        $query = AdminLog::with('administrador:id,nom');

        // C. Filtrar per data_desde
        if ($dataDesde !== '0' && $dataDesde !== '-') {
            $query->where('created_at', '>=', $dataDesde);
        }
        // D. Filtrar per data_fins
        if ($dataFins !== '0' && $dataFins !== '-') {
            $query->where('created_at', '<=', $dataFins);
        }
        // E. Filtrar per administrador_id
        if ($administradorId !== '0' && $administradorId !== '-') {
            $query->where('administrador_id', (int) $administradorId);
        }
        // F. Filtrar per accio
        if ($accio !== '-' && $accio !== '0') {
            $query->where('accio', 'ilike', '%'.$accio.'%');
        }
        // G. Filtrar per cerca global
        if ($cerca !== '-' && $cerca !== '0' && $cerca !== '') {
            $query->where(function ($q) use ($cerca) {
                $q->where('detall', 'ilike', '%'.$cerca.'%')
                    ->orWhereRaw("abans::text ilike ?", ['%'.$cerca.'%'])
                    ->orWhereRaw("despres::text ilike ?", ['%'.$cerca.'%']);
            });
        }

        $query->orderByDesc('created_at');
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = [];
        // H. Recórrer resultats i normalitzar format
        foreach ($paginator->getCollection() as $log) {
            $createdAtStr = null;
            // H1. Si hi ha created_at, convertir-lo
            if ($log->created_at !== null) {
                $createdAtStr = $log->created_at->toIso8601String();
            }
            $adminNom = 'Admin';
            // H2. Si hi ha administrador i nom, usar-lo
            if ($log->administrador !== null && $log->administrador->nom !== null) {
                $adminNom = $log->administrador->nom;
            }
            $data[] = [
                'id' => $log->id,
                'created_at' => $createdAtStr,
                'administrador_nom' => $adminNom,
                'accio' => $log->accio,
                'detall' => $log->detall,
                'abans' => $log->abans,
                'despres' => $log->despres,
                'ip' => $log->ip,
            ];
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }
}
