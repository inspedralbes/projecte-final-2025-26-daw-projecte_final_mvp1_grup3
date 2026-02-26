<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\AdminNotificacio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador de notificacions de l'admin.
 * Llistat paginat i marcar com a llegida.
 */
class AdminNotificacioController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista notificacions paginades amb filtre llegida.
     * Pas A: Obtenir paràmetres de la ruta (page, per_page, llegida).
     * Pas B: Construir query amb filtre opcional.
     * Pas C: Retornar JSON paginat.
     */
    public function index(Request $request, int $page = 1, int $perPage = 20, $llegida = '-'): JsonResponse
    {
        $administradorId = $request->admin_id;
        // A1. Si no hi ha administrador, denegar accés
        if (!$administradorId) {
            return response()->json(['error' => 'No autoritzat'], 401);
        }

        // A2. Normalitzar perPage
        if ($perPage < 1) {
            $perPage = 20;
        }
        // A3. Normalitzar page
        if ($page < 1) {
            $page = 1;
        }

        // B. Query base per administrador
        $query = AdminNotificacio::where('administrador_id', $administradorId);

        // B1. Si hi ha filtre de llegida, aplicar-lo
        if ($llegida !== '-' && $llegida !== '0') {
            // B1.1. Filtre per no llegides
            if ($llegida === '1' || $llegida === 'false') {
                $query->where('llegida', false);
            }
            // B1.2. Filtre per llegides
            if ($llegida === '2' || $llegida === 'true') {
                $query->where('llegida', true);
            }
        }

        $query->orderByDesc('data');
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // C. Retornar resposta
        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ],
        ]);
    }

    /**
     * Marca una notificació com a llegida.
     */
    public function marcarLlegida(Request $request, int $id): JsonResponse
    {
        $administradorId = $request->admin_id;
        // A1. Si no hi ha administrador, denegar accés
        if (!$administradorId) {
            return response()->json(['error' => 'No autoritzat'], 401);
        }

        $notificacio = AdminNotificacio::where('id', $id)
            ->where('administrador_id', $administradorId)
            ->first();

        // B1. Si no existeix la notificació, retornar 404
        if ($notificacio === null) {
            return response()->json(['error' => 'Notificació no trobada'], 404);
        }

        $notificacio->llegida = true;
        $notificacio->save();

        return response()->json(['success' => true, 'data' => $notificacio]);
    }
}
