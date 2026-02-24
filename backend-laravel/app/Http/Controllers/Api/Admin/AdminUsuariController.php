<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador d'usuaris i administradors per l'admin.
 * tipus: usuaris | admins
 * prohibit: 0=tots, 1=prohibits, 2=actius
 */
class AdminUsuariController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista usuaris o administradors paginats.
     */
    public function index(
        string $tipus = 'usuaris',
        int $page = 1,
        int $perPage = 20,
        $prohibit = '0',
        $cerca = '-'
    ): JsonResponse {
        if ($perPage < 1) {
            $perPage = 20;
        }
        if ($page < 1) {
            $page = 1;
        }

        if ($tipus === 'admins' || $tipus === 'admin') {
            $query = Administrador::query();
        } else {
            $query = User::query();

            if ($prohibit === '1') {
                $query->where('prohibit', true);
            }
            if ($prohibit === '2') {
                $query->where('prohibit', false);
            }
            if ($cerca !== '-' && $cerca !== '0' && $cerca !== '') {
                $query->where(function ($q) use ($cerca) {
                    $q->where('nom', 'ilike', '%'.$cerca.'%')
                        ->orWhere('email', 'ilike', '%'.$cerca.'%');
                });
            }
        }

        $paginator = $query->orderBy('id')->paginate($perPage, ['*'], 'page', $page);

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
     * Prohibir o desprohibir un usuari.
     */
    public function prohibir(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'prohibit' => 'required|boolean',
            'motiu' => 'required_if:prohibit,true|nullable|string',
        ]);

        $usuari = User::find($id);
        if ($usuari === null) {
            return response()->json(['error' => 'Usuari no trobat'], 404);
        }

        $abans = [
            'prohibit' => $usuari->prohibit,
            'motiu_prohibicio' => $usuari->motiu_prohibicio,
        ];

        $usuari->prohibit = $request->boolean('prohibit');
        if ($request->boolean('prohibit')) {
            $usuari->data_prohibicio = now();
            $usuari->motiu_prohibicio = $request->input('motiu');
        } else {
            $usuari->data_prohibicio = null;
            $usuari->motiu_prohibicio = null;
        }
        $usuari->save();

        $despres = [
            'prohibit' => $usuari->prohibit,
            'motiu_prohibicio' => $usuari->motiu_prohibicio,
        ];

        $accioLog = 'Desprohibir usuari';
        if ($usuari->prohibit) {
            $accioLog = 'Prohibir usuari';
        }
        $adminLogService = app(\App\Services\AdminLogService::class);
        $adminLogService->registrar(
            1,
            $accioLog,
            'Usuari ID '.$id.': '.$usuari->nom,
            $abans,
            $despres,
            $request->ip()
        );

        return response()->json(['success' => true, 'data' => $usuari]);
    }
}
