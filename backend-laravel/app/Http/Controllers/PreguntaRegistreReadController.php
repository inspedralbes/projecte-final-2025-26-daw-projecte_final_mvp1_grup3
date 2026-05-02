<?php

namespace App\Http\Controllers;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Resources\PreguntaRegistreResource;
use App\Services\PreguntaRegistreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per les preguntes de registre.
 *
 * Operacions:
 *   - READ: index (preguntes per categoria_id)
 */
class PreguntaRegistreReadController extends Controller
{
    /**
     * Servei de preguntes de registre.
     *
     * @var PreguntaRegistreService
     */
    protected PreguntaRegistreService $preguntaRegistreService;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Constructor. Injecció del servei.
     */
    public function __construct(PreguntaRegistreService $preguntaRegistreService)
    {
        $this->preguntaRegistreService = $preguntaRegistreService;
    }

    /**
     * READ. Obté totes les preguntes d'una categoria.
     */
    public function index(Request $request, int $categoria_id): JsonResponse
    {
        if ($categoria_id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'L\'ID de categoria ha de ser un enter positiu.',
            ], 400);
        }

        try {
            $preguntes = $this->preguntaRegistreService->obtenirPreguntesPerCategoria($categoria_id);

            return response()->json([
                'success' => true,
                'preguntes' => PreguntaRegistreResource::collection($preguntes)->resolve($request)['data'] ?? [],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
