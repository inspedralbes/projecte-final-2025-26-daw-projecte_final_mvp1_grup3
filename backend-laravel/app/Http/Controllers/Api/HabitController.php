<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\HabitResource;
use App\Models\Habit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per la lectura d'hàbits.
 * Només mètodes GET (index, show). Creació i actualització via Redis.
 */
class HabitController extends Controller
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista els hàbits de l'usuari autenticat.
     *
     * A. Comprova que hi ha usuari autenticat.
     * B. Filtra els hàbits per usuari_id.
     * C. Retorna la col·lecció transformada amb HabitResource.
     */
    public function index(Request $request): JsonResponse
    {
        // A. Comprovar autenticació
        $usuariId = auth()->id();

        if ($usuariId === null) {
            return response()->json(['error' => 'No autenticat'], 401);
        }

        // B. Filtrar hàbits per usuari i obtenir resultats
        $habits = Habit::where('usuari_id', $usuariId)->get();

        // C. Retornar resposta amb el recurs
        return HabitResource::collection($habits)->toResponse($request);
    }

    /**
     * Retorna un únic hàbit per ID.
     *
     * A. Comprova que hi ha usuari autenticat.
     * B. Cerca l'hàbit i verifica que pertany a l'usuari.
     * C. Retorna l'hàbit transformat o 404.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        // A. Comprovar autenticació
        $usuariId = auth()->id();

        if ($usuariId === null) {
            return response()->json(['error' => 'No autenticat'], 401);
        }

        // B. Cercar hàbit i comprovar propietat
        $habit = Habit::where('id', $id)->where('usuari_id', $usuariId)->first();

        if ($habit === null) {
            return response()->json(['error' => 'Hàbit no trobat'], 404);
        }

        // C. Retornar resposta amb el recurs
        return (new HabitResource($habit))->toResponse($request);
    }
}
