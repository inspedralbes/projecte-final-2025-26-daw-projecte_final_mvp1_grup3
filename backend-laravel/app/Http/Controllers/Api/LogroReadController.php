<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\LogroResource;
use App\Services\LogroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per la llista de logros i medalles.
 *
 * Operacions:
 *   - READ: index (tots els logros)
 */
class LogroReadController extends Controller
{
    /**
     * Servei de logros.
     *
     * @var LogroService
     */
    protected LogroService $logroService;

    /**
     * Constructor. Injecció del servei.
     */
    public function __construct(LogroService $logroService)
    {
        $this->logroService = $logroService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * READ. Llista tots els logros i medalles disponibles.
     */
    public function index(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $logros = $this->logroService->llistarTotsElsLogros($usuariId);

        return LogroResource::collection($logros)->toResponse($request);
    }
}
