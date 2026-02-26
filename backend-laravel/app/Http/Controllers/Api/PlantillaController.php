<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\PlantillaResource;
use App\Models\Plantilla;
use App\Services\PlantillaService; // Import PlantillaService
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per la lectura de plantilles.
 * Només mètodes GET (index, show). Creació i actualització via Redis.
 */
class PlantillaController extends Controller
{
    protected $plantillaService;

    public function __construct(PlantillaService $plantillaService)
    {
        $this->plantillaService = $plantillaService;
    }
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista les plantilles disponibles per a l'usuari.
     * Inclou plantilles pròpies i públiques, o només les pròpies segons el filtre.
     */
    public function index(Request $request): JsonResponse
    {
        // A. Obtindre paràmetres de filtre de la request
        // El filtre 'my' o 'all' es manté com a query parameter per no modificar les rutes fora de l'abast.
        $filter = $request->query('filter', 'all'); // 'all' o 'my'

        // B. Usuari autenticat (injectat pel middleware ensure.user)
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        // C. Obtenir plantilles usant el servei amb els filtres i l'usuari autenticat
        $plantilles = $this->plantillaService->getPlantilles( $filter, $usuariId)->load('habits');

        // D. Retornar resposta amb el recurs
        return PlantillaResource::collection($plantilles)->toResponse($request);
    }

    /**
     * Retorna una plantilla per ID (propia o pública).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // A. Usuari autenticat (injectat pel middleware ensure.user)
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        // B. Cercar plantilla i comprovar propietat o visibilitat pública
        $plantilla = Plantilla::where('id', $id)
            ->where(function ($query) use ($usuariId) {
                $query->where('creador_id', $usuariId)
                    ->orWhere('es_publica', true);
            })
            ->with('habits') // Eager load habits
            ->first();

        if ($plantilla === null) {
            return response()->json(['error' => 'Plantilla no trobada'], 404);
        }

        // C. Retornar resposta amb el recurs
        return (new PlantillaResource($plantilla))->toResponse($request);
    }
}
