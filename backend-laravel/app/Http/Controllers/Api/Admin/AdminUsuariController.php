<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\Administrador;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        // A. Normalitzar per_page
        if ($perPage < 1) {
            $perPage = 20;
        }
        // B. Normalitzar page
        if ($page < 1) {
            $page = 1;
        }

        // C. Determinar tipus de consulta (admins o usuaris)
        if ($tipus === 'admins' || $tipus === 'admin') {
            $query = Administrador::query();
        } else {
            $query = User::query();

            // Filtre de prohibició (1=prohibits, 2=actius, altres=tots)
            // C1. Si es demanen prohibits, aplicar filtre
            if ($prohibit === '1' || $prohibit === 'true') {
                $query->where('prohibit', true);
            }
            // C2. Si es demanen actius, aplicar filtre
            if ($prohibit === '2' || $prohibit === 'false') {
                // A l'admin/usuaris.vue el frontend envia 'false' per defecte per a Tots/Actius
                // Si realment volem filtrar només els actius quan envia 'false':
                // C2.1. Si és l'opció explícita "2", filtrar actius
                if ($prohibit === '2') {
                    $query->where('prohibit', false);
                }
            }

            // Cerca per nom o email
            // C3. Si hi ha cerca, aplicar-la
            if ($cerca !== '-' && $cerca !== '0' && $cerca !== '' && $cerca !== 'none') {
                $query->where(function ($q) use ($cerca) {
                    $q->where('nom', 'ilike', '%' . $cerca . '%')
                        ->orWhere('email', 'ilike', '%' . $cerca . '%');
                });
            }
        }

        $paginator = $query->orderBy('id')->paginate($perPage, ['*'], 'page', $page);

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

    /**
     * Prohibir o desprohibir un usuari.
     */
    public function prohibir(Request $request, int $id): JsonResponse
    {
        // A. Validació de paràmetres d'entrada
        $request->validate([
            'prohibit' => 'required|boolean',
            'motiu' => 'required_if:prohibit,true|nullable|string',
        ]);

        $usuari = User::find($id);
        // A1. Si l'usuari no existeix, retornar 404
        if ($usuari === null) {
            return response()->json(['error' => 'Usuari no trobat'], 404);
        }

        $abans = [
            'prohibit' => $usuari->prohibit,
            'motiu_prohibicio' => $usuari->motiu_prohibicio,
        ];

        $usuari->prohibit = $request->boolean('prohibit');
        // B. Si és prohibició, assignar motiu i data
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
        // C. Determinar acció per al log
        if ($usuari->prohibit) {
            $accioLog = 'Prohibir usuari';
        }
        $adminLogService = app(\App\Services\AdminLogService::class);
        $adminLogService->registrar(
            1,
            $accioLog,
            'Usuari ID ' . $id . ': ' . $usuari->nom,
            $abans,
            $despres,
            $request->ip()
        );

        return response()->json(['success' => true, 'data' => $usuari]);
    }
}
