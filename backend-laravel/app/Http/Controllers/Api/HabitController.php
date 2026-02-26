<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\HabitResource;
use App\Models\Habit;
use App\Models\UsuariHabit;
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
     * Inclou hàbits propis i assignats via USUARIS_HABITS.
     */
    public function index(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        // A1. Si no hi ha usuari, denegar accés
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');
        $habits = Habit::where('usuari_id', $usuariId)
            ->orWhereIn('id', $habitIdsAssignats)
            ->get();

        return HabitResource::collection($habits)->toResponse($request);
    }

    /**
     * Retorna un únic hàbit per ID.
     * Verifica que l'hàbit pertanyi a l'usuari (propietari o assignat).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $usuariId = $request->user_id;
        // A1. Si no hi ha usuari, denegar accés
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $habit = Habit::where('id', $id)
            ->where(function ($q) use ($usuariId) {
                $q->where('usuari_id', $usuariId)
                    ->orWhereIn('id', UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id'));
            })
            ->first();

        // B2. Si no existeix l'hàbit, retornar 404
        if ($habit === null) {
            return response()->json(['error' => 'Hàbit no trobat'], 404);
        }

        return (new HabitResource($habit))->toResponse($request);
    }
}
