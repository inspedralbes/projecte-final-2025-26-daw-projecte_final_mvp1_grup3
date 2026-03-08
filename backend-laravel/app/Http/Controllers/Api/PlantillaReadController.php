<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\PlantillaResource;
use App\Models\Plantilla;
use App\Services\PlantillaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per la lectura de plantilles.
 *
 * Operacions:
 *   - READ: index, show (GET)
 *   - CREATE/UPDATE/DELETE: via Socket.io → Redis → Worker (no en aquest controller)
 */
class PlantillaReadController extends Controller
{
    /**
     * Servei de plantilles.
     *
     * @var PlantillaService
     */
    protected PlantillaService $plantillaService;

    /**
     * Constructor. Injecció del servei.
     */
    public function __construct(PlantillaService $plantillaService)
    {
        $this->plantillaService = $plantillaService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * READ. Llista les plantilles disponibles per a l'usuari (filtre all/my).
     */
    public function index(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'all');

        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $plantilles = $this->plantillaService->getPlantilles($filter, $usuariId)->load('habits');

        return PlantillaResource::collection($plantilles)->toResponse($request);
    }

    /**
     * READ. Retorna una plantilla per ID (propia o pública).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $plantilla = Plantilla::where('id', $id)
            ->where(function ($query) use ($usuariId) {
                $query->where('creador_id', $usuariId)
                    ->orWhere('es_publica', true);
            })
            ->with('habits')
            ->first();

        if ($plantilla === null) {
            return response()->json(['error' => 'Plantilla no trobada'], 404);
        }

        return (new PlantillaResource($plantilla))->toResponse($request);
    }
}
