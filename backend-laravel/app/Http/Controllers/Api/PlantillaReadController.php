<?php

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\PlantillaResource;
use App\Domains\Habits\Services\HabitService;
use App\Domains\Plantilla\Services\PlantillaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador API per la lectura de plantilles.
 *
 * Operacions:
 *   - READ: index, show (GET)
 *   - CREATE/UPDATE/DELETE: via Socket.io → Redis → Worker (no en aquest controller)
 */
class PlantillaReadController extends Controller
{
    /**
     * Servei de plantilles.
     *
     * @var PlantillaService
     */
    protected PlantillaService $plantillaService;

    protected HabitService $habitService;

    /**
     * Constructor. Injecció del servei.
     */
    public function __construct(PlantillaService $plantillaService, HabitService $habitService)
    {
        $this->plantillaService = $plantillaService;
        $this->habitService = $habitService;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * READ. Llista les plantilles disponibles per a l'usuari (filtre all/my).
     */
    public function index(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'all');

        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $plantilles = $this->plantillaService->getPlantilles($filter, $usuariId)->load('habits');

        return PlantillaResource::collection($plantilles)->toResponse($request);
    }

    /**
     * READ. Retorna una plantilla per ID (propia, pública o d'un amic).
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $plantilla = $this->plantillaService->getPlantillaVisiblePerUsuari($id, $usuariId);

        if ($plantilla === null) {
            return response()->json(['error' => 'Plantilla no trobada'], 404);
        }

        return (new PlantillaResource($plantilla))->toResponse($request);
    }

    /**
     * Importa hàbits seleccionats d'una plantilla (p. ex. compartida per xat d'amics).
     */
    public function importHabits(Request $request, int $id): JsonResponse
    {
        $usuariId = $request->user_id;
        if (!$usuariId) {
            return response()->json(['message' => 'No autoritzat'], 401);
        }

        $validated = $request->validate([
            'habit_ids' => 'required|array|min:1',
            'habit_ids.*' => 'integer',
        ]);

        $plantilla = $this->plantillaService->getPlantillaVisiblePerUsuari($id, (int) $usuariId);
        if ($plantilla === null) {
            return response()->json(['message' => 'Plantilla no trobada'], 404);
        }

        $permessos = $plantilla->habits->pluck('id')->map(function ($habitId) {
            return (int) $habitId;
        })->all();

        $seleccionats = [];
        foreach ($validated['habit_ids'] as $habitId) {
            $hid = (int) $habitId;
            if (in_array($hid, $permessos, true) && !in_array($hid, $seleccionats, true)) {
                $seleccionats[] = $hid;
            }
        }

        if ($seleccionats === []) {
            return response()->json(['message' => 'Cap hàbit vàlid seleccionat'], 422);
        }

        $resultat = $this->habitService->exportarHabitsDePlantilla((int) $usuariId, $id, $seleccionats);

        if (empty($resultat['success'])) {
            return response()->json([
                'success' => false,
                'message' => $resultat['message'] ?? 'Error en importar hàbits',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'habits' => $resultat['habits'] ?? [],
        ]);
    }
}
