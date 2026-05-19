<?php

namespace App\Http\Controllers\Api\Admin;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminHabitResource;
use App\Models\Habit;
use App\Services\AdminLogService;
use App\Services\RedisFeedbackService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Controlador d'hàbits per l'admin.
 * Llista via HTTP; creació / edició / eliminació via HTTP (i opcionalment via Socket + cua Redis).
 */
class AdminHabitController extends Controller
{
    public function __construct(
        private AdminLogService $adminLogService,
        private RedisFeedbackService $feedbackService
    ) {}

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Llista hàbits paginats.
     */
    public function index(int $page = 1, int $perPage = 20): JsonResponse
    {
        if ($perPage < 1) {
            $perPage = 20;
        }
        if ($page < 1) {
            $page = 1;
        }

        $paginator = Habit::with(['usuari:id,nom,email', 'plantilla:id,titol,categoria'])
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $items = AdminHabitResource::collection($paginator->items())->resolve(request());
        $dataArray = $items['data'] ?? $items;

        return response()->json([
            'success' => true,
            'data' => [
                'data' => $dataArray,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                ],
            ],
        ]);
    }

    /**
     * Crear un hàbit (HTTP directe; no cal worker Redis).
     */
    public function store(Request $request): JsonResponse
    {
        $adminId = (int) $request->input('admin_id');
        $validated = $request->validate([
            'titol' => 'required|string|max:255',
            'usuari_id' => 'required|integer|min:1',
            'categoria_id' => 'nullable|integer|min:1',
            'plantilla_id' => 'nullable|integer',
            'dificultat' => 'nullable|string|max:32',
            'frequencia_tipus' => 'nullable|string|max:32',
            'dies_setmana' => 'nullable|string|max:32',
            'objectiu_vegades' => 'nullable|integer|min:1',
            'moment_dia' => 'nullable|string|max:32',
            'unitat' => 'nullable|string|max:64',
            'icona' => 'nullable|string|max:32',
            'color' => 'nullable|string|max:16',
        ]);

        try {
            $habit = DB::transaction(function () use ($adminId, $validated) {
                $habit = Habit::create([
                    'usuari_id' => $validated['usuari_id'],
                    'plantilla_id' => $validated['plantilla_id'] ?? null,
                    'categoria_id' => $validated['categoria_id'] ?? null,
                    'titol' => $validated['titol'],
                    'dificultat' => $validated['dificultat'] ?? 'media',
                    'frequencia_tipus' => $validated['frequencia_tipus'] ?? 'diaria',
                    'dies_setmana' => Habit::diesSetmanaCsvAJsonPg($validated['dies_setmana'] ?? null),
                    'objectiu_vegades' => $validated['objectiu_vegades'] ?? 1,
                    'moment_dia' => $validated['moment_dia'] ?? 'tot_dia',
                    'unitat' => $validated['unitat'] ?? 'vegades',
                    'icona' => $validated['icona'] ?? '🏃',
                    'color' => $validated['color'] ?? '#65A30D',
                ]);

                $despres = $habit->toArray();
                $this->adminLogService->registrar($adminId, 'Crear habit', 'Habit ID ' . $habit->id . ': ' . $habit->titol, null, $despres, null);
                $this->feedbackService->publicarPayload([
                    'admin_id' => $adminId,
                    'entity' => 'habit',
                    'action' => 'CREATE',
                    'success' => true,
                    'data' => $despres,
                ]);

                return $habit;
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $habit->load(['usuari:id,nom,email', 'plantilla:id,titol,categoria']);

        return response()->json([
            'success' => true,
            'data' => (new AdminHabitResource($habit))->resolve(request()),
        ], 201);
    }

    /**
     * Actualitzar un hàbit.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $adminId = (int) $request->input('admin_id');
        $validated = $request->validate([
            'titol' => 'sometimes|string|max:255',
            'categoria_id' => 'nullable|integer|min:1',
            'dificultat' => 'nullable|string|max:32',
            'frequencia_tipus' => 'nullable|string|max:32',
            'dies_setmana' => 'nullable|string|max:32',
            'objectiu_vegades' => 'nullable|integer|min:1',
            'moment_dia' => 'nullable|string|max:32',
            'unitat' => 'nullable|string|max:64',
            'icona' => 'nullable|string|max:32',
            'color' => 'nullable|string|max:16',
        ]);

        $habit = Habit::find($id);
        if ($habit === null) {
            return response()->json(['success' => false, 'message' => 'Habit no trobat'], 404);
        }

        try {
            DB::transaction(function () use ($adminId, $habit, $validated) {
                $abans = $habit->toArray();
                if (array_key_exists('titol', $validated)) {
                    $habit->titol = $validated['titol'];
                }
                if (array_key_exists('categoria_id', $validated)) {
                    $habit->categoria_id = $validated['categoria_id'];
                }
                if (array_key_exists('dificultat', $validated)) {
                    $habit->dificultat = $validated['dificultat'];
                }
                if (array_key_exists('frequencia_tipus', $validated)) {
                    $habit->frequencia_tipus = $validated['frequencia_tipus'];
                }
                if (array_key_exists('dies_setmana', $validated)) {
                    $habit->dies_setmana = Habit::diesSetmanaCsvAJsonPg((string) $validated['dies_setmana']);
                }
                if (array_key_exists('objectiu_vegades', $validated)) {
                    $habit->objectiu_vegades = $validated['objectiu_vegades'];
                }
                if (array_key_exists('moment_dia', $validated)) {
                    $habit->moment_dia = $validated['moment_dia'];
                }
                if (array_key_exists('unitat', $validated)) {
                    $habit->unitat = $validated['unitat'];
                }
                if (array_key_exists('icona', $validated)) {
                    $habit->icona = $validated['icona'];
                }
                if (array_key_exists('color', $validated)) {
                    $habit->color = $validated['color'];
                }
                $habit->save();
                $despres = $habit->toArray();
                $this->adminLogService->registrar($adminId, 'Editar habit', 'Habit ID ' . $habit->id . ': ' . $habit->titol, $abans, $despres, null);
                $this->feedbackService->publicarPayload([
                    'admin_id' => $adminId,
                    'entity' => 'habit',
                    'action' => 'UPDATE',
                    'success' => true,
                    'data' => $despres,
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $habit->refresh();
        $habit->load(['usuari:id,nom,email', 'plantilla:id,titol,categoria']);

        return response()->json([
            'success' => true,
            'data' => (new AdminHabitResource($habit))->resolve(request()),
        ]);
    }

    /**
     * Eliminar un hàbit.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $adminId = (int) $request->input('admin_id');
        $habit = Habit::find($id);
        if ($habit === null) {
            return response()->json(['success' => false, 'message' => 'Habit no trobat'], 404);
        }

        try {
            DB::transaction(function () use ($adminId, $habit) {
                $abans = $habit->toArray();
                $habitId = $habit->id;
                $habit->delete();
                $this->adminLogService->registrar($adminId, 'Eliminar habit', 'Habit ID ' . $habitId, $abans, null, null);
                $this->feedbackService->publicarPayload([
                    'admin_id' => $adminId,
                    'entity' => 'habit',
                    'action' => 'DELETE',
                    'success' => true,
                    'data' => ['id' => $habitId],
                ]);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['success' => true, 'data' => ['id' => $id]]);
    }
}
