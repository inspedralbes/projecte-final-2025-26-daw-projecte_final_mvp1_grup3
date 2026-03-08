<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\HabitProgressLogResource;
use App\Http\Resources\HabitProgressTodayResource;
use App\Http\Resources\HabitResource;
use App\Models\Habit;
use App\Models\UsuariHabit;
use App\Services\HabitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per la lectura d'hàbits de l'usuari.
 *
 * Operacions:
 *   - READ: index, show, indexAll, progress, logs (GET)
 *   - CREATE/UPDATE/DELETE: via Socket.io → Redis → Worker (no en aquest controller)
 *
 * @see HabitQueueHandler Per operacions CUD
 */
class HabitReadController extends Controller
{
    /**
     * Servei d'hàbits.
     *
     * @var HabitService
     */
    protected HabitService $habitService;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Constructor. Injecció del servei.
     */
    public function __construct(HabitService $habitService)
    {
        $this->habitService = $habitService;
    }

    /**
     * READ. Llista els hàbits del dia de l'usuari autenticat.
     * Inclou hàbits propis i assignats via USUARIS_HABITS.
     */
    public function index(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $diaIndex = (int) now()->dayOfWeekIso; // 1..7 (Dilluns..Diumenge)
        $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');
        $query = Habit::where('usuari_id', $usuariId)
            ->orWhereIn('id', $habitIdsAssignats);
        $query->where(function ($q) use ($diaIndex) {
            $q->whereNull('dies_setmana')
                ->orWhereRaw('dies_setmana[' . $diaIndex . '] = true');
        });
        $habits = $query->get();

        return HabitResource::collection($habits)->toResponse($request);
    }

    /**
     * READ. Retorna un únic hàbit per ID.
     * Verifica que l'hàbit pertanyi a l'usuari (propietari o assignat).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $diaIndex = (int) now()->dayOfWeekIso; // 1..7
        $habit = Habit::where('id', $id)
            ->where(function ($q) use ($usuariId) {
                $q->where('usuari_id', $usuariId)
                    ->orWhereIn('id', UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id'));
            })
            ->where(function ($q) use ($diaIndex) {
                $q->whereNull('dies_setmana')
                    ->orWhereRaw('dies_setmana[' . $diaIndex . '] = true');
            })
            ->first();

        if ($habit === null) {
            return response()->json(['error' => 'Hàbit no trobat'], 404);
        }

        return (new HabitResource($habit))->toResponse($request);
    }

    /**
     * READ. Llista tots els hàbits sense filtrar per dies.
     */
    public function indexAll(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
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
     * READ. Retorna el progrés d'avui per a tots els hàbits de l'usuari.
     */
    public function progress(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $resultat = $this->habitService->obtenirProgresAvui($usuariId);

        return (new HabitProgressTodayResource($resultat))->toResponse($request);
    }

    /**
     * READ. Retorna logs diaris agregats per hàbit.
     */
    public function logs(Request $request): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $resultat = $this->habitService->obtenirLogsHistorics($usuariId);

        return (new HabitProgressLogResource($resultat))->toResponse($request);
    }
}
