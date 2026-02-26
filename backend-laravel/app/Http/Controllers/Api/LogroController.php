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
 * Utilitza el LogroService per obtenir les dades.
 */
class LogroController extends Controller
{
    /**
     * Servei de logros.
     *
     * @var LogroService
     */
    protected LogroService $logroService;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(LogroService $logroService)
    {
        $this->logroService = $logroService;
    }

    /**
     * Llista tots els logros i medalles disponibles.
     *
     * A. Crida al servei per obtenir la col·lecció de logros.
     * B. Transforma la col·lecció utilitzant LogroResource.
     * C. Retorna la resposta JSON.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        // A1. Si no hi ha usuari, denegar accés
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }
        // B. Recuperar logros des del servei
        $logros = $this->logroService->llistarTotsElsLogros($usuariId);
        // C. Retornar resposta amb recursos
        return LogroResource::collection($logros)->toResponse($request);
    }
}
