<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\PlantillaResource;
use App\Models\Plantilla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per la lectura de plantilles.
 * Només mètodes GET (index, show). Creació i actualització via Redis.
 */
class PlantillaController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista les plantilles disponibles per a l'usuari.
     * Inclou plantilles pròpies i públiques.
     */
    public function index(Request $request): JsonResponse
    {
        // A. Usuari per defecte sense autenticació (id 1)
        $usuariId = 1;

        // B. Filtrar plantilles per usuari o públiques
        $plantilles = Plantilla::where('creador_id', $usuariId)
            ->orWhere('es_publica', true)
            ->get();

        // C. Retornar resposta amb el recurs
        return PlantillaResource::collection($plantilles)->toResponse($request);
    }

    /**
     * Retorna una plantilla per ID (propia o pública).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // A. Usuari per defecte sense autenticació (id 1)
        $usuariId = 1;

        // B. Cercar plantilla i comprovar propietat o visibilitat pública
        $plantilla = Plantilla::where('id', $id)
            ->where(function ($query) use ($usuariId) {
                $query->where('creador_id', $usuariId)
                    ->orWhere('es_publica', true);
            })
            ->first();

        if ($plantilla === null) {
            return response()->json(['error' => 'Plantilla no trobada'], 404);
        }

        // C. Retornar resposta amb el recurs
        return (new PlantillaResource($plantilla))->toResponse($request);
    }
}
