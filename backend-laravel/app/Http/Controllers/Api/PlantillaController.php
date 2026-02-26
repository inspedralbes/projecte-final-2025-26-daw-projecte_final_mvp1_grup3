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
 * Només mètodes GET (index, show). Creació i actualització via Redis.
 */
class PlantillaController extends Controller
{
    /**
     * Servei de plantilles.
     *
     * @var PlantillaService
     */
    protected PlantillaService $plantillaService;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(PlantillaService $plantillaService)
    {
        $this->plantillaService = $plantillaService;
    }

    /**
     * Llista les plantilles disponibles per a l'usuari.
     * Inclou plantilles pròpies i públiques, o només les pròpies segons el filtre.
     */
    public function index(Request $request): JsonResponse
    {
        // A. Obtindre paràmetres de filtre de la request
        // A1. El filtre 'my' o 'all' es manté com a query parameter per no modificar les rutes fora de l'abast.
        $filtre = $request->query('filter', 'all');

        // B. Usuari autenticat (injectat pel middleware ensure.user)
        $usuariId = $request->user_id;
        // B1. Si no hi ha usuari, denegar accés
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        // C. Obtenir plantilles usant el servei amb els filtres i l'usuari autenticat
        $plantilles = $this->plantillaService->getPlantilles($filtre, $usuariId)->load('habits');

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
        // A1. Si no hi ha usuari, denegar accés
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        // B. Cercar plantilla i comprovar propietat o visibilitat pública
        $plantilla = Plantilla::where('id', $id)
            ->where(function ($query) use ($usuariId) {
                $query->where('creador_id', $usuariId)
                    ->orWhere('es_publica', true);
            })
            // B1. Carregar hàbits associats
            ->with('habits')
            ->first();

        // B2. Si no existeix la plantilla, retornar 404
        if ($plantilla === null) {
            return response()->json(['error' => 'Plantilla no trobada'], 404);
        }

        // C. Retornar resposta amb el recurs
        return (new PlantillaResource($plantilla))->toResponse($request);
    }
}
