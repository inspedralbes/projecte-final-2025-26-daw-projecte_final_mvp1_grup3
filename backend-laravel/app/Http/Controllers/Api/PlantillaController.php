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
 * Només mètodes GET (index, show, recommend).
 */
class PlantillaController extends Controller
{
    /**
     * Servei de plantilles.
     * @var PlantillaService
     */
    protected $plantillaService;

    /**
     * Constructor. Injecció del servei.
     */
    public function __construct(PlantillaService $plantillaService)
    {
        $this->plantillaService = $plantillaService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista les plantilles disponibles per a l'usuari.
     * Inclou plantilles pròpies i públiques, o només les pròpies segons el filtre.
     *
     * A. Obté el filtre de la petició (per defecte 'all').
     * B. Defineix l'usuari per defecte (id 1).
     * C. Crida al servei per obtenir les plantilles.
     * D. Retorna la col·lecció de plantilles com a recurs JSON.
     */
    public function index(Request $request): JsonResponse
    {
        // A. Obtenir paràmetres de filtre
        $filter = $request->query('filter', 'all');

        // B. Usuari per defecte (administrador id 1)
        $usuariId = 1;

        // C. Obtenir plantilles mitjançant el servei
        $plantilles = $this->plantillaService->getPlantilles($filter, $usuariId)->load('habits');

        // D. Retornar resposta
        return PlantillaResource::collection($plantilles)->toResponse($request);
    }

    /**
     * Retorna una plantilla per ID (pròpia o pública).
     *
     * A. Defineix l'usuari per defecte (id 1).
     * B. Cerca la plantilla validant visibilitat (propietari o pública).
     * C. Valida si la plantilla existeix.
     * D. Retorna el recurs de la plantilla o error 404.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // A. Usuari per defecte
        $usuariId = 1;

        // B. Cercar plantilla amb validació de visibilitat
        $plantilla = Plantilla::where('id', $id)
            ->where(function ($query) use ($usuariId) {
                $query->where('creador_id', $usuariId)
                    ->orWhere('es_publica', true);
            })
            ->with('habits')
            ->first();

        // C. Validar existència
        if ($plantilla === null) {
            return response()->json(['error' => 'Plantilla no trobada'], 404);
        }

        // D. Retornar recurs
        return (new PlantillaResource($plantilla))->toResponse($request);
    }

    /**
     * Retorna una plantilla recomanada per a una categoria.
     *
     * A. Delega la cerca al servei de plantilles.
     * B. Valida si s'ha trobat cap recomanació.
     * C. Retorna la resposta JSON amb la plantilla i els seus hàbits.
     */
    public function recommend(int $categoria_id): JsonResponse
    {
        // A. Obtenir recomanació del servei
        $plantilla = $this->plantillaService->getRecommendedPlantilla($categoria_id);

        // B. Validar si existeix recomanació
        if (!$plantilla) {
            return response()->json([
                'success' => false, 
                'message' => 'No s\'ha trobat cap plantilla recomanada per aquesta categoria'
            ], 404);
        }

        // C. Retornar resposta amb la plantilla carregada
        return response()->json([
            'success' => true,
            'plantilla' => new PlantillaResource($plantilla->load('habits'))
        ]);
    }
}
