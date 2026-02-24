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
    protected $logroService;

    public function __construct(LogroService $logroService)
    {
        $this->logroService = $logroService;
    }

    //================================ MÈTODES / FUNCIONS ===========

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
        // A. Obtenir dades del servei
        $logros = $this->logroService->llistarTotsElsLogros();

        // B. Transformar dades amb el Recurs
        // C. Retornar la resposta
        return LogroResource::collection($logros)->toResponse($request);
    }
}
