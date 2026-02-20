<?php

namespace App\Http\Controllers;

//================================ NAMESPACES / IMPORTS ============

use App\Services\PreguntaRegistreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per les preguntes de registre.
 * Retorna les preguntes segons la categoria seleccionada.
 */
class PreguntaRegistreController extends Controller
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
     * Obté totes les preguntes d'una categoria.
     *
     * A. Valida que categoria_id del path sigui vàlid.
     * B. Delega al servei per obtenir les preguntes.
     * C. Retorna la resposta JSON amb les preguntes.
     *
     * @param  Request  $request
     * @param  int  $categoria_id  ID de la categoria (paràmetre de ruta)
     * @return JsonResponse
     */
    public function index(Request $request, int $categoria_id): JsonResponse
    {
        // A. Validar que l'ID sigui positiu
        if ($categoria_id <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'L\'ID de categoria ha de ser un enter positiu.',
            ], 400);
        }

        try {
            // B. Obtenir preguntes mitjançant el servei
            $preguntes = $this->preguntaRegistreService->obtenirPreguntesPerCategoria($categoria_id);

            // C. Retornar resposta JSON
            return response()->json([
                'success' => true,
                'preguntes' => $preguntes,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
