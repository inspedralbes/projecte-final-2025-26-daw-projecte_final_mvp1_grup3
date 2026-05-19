<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Social\Actions\ImportHabitFromPostAction;
use App\Domains\Social\Actions\ImportPlantillaFromPostAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

//================================ CONTROLLER ====================

/**
 * SocialImportController (thin).
 */
class SocialImportController extends Controller
{
    private ImportHabitFromPostAction $importHabitAction;

    private ImportPlantillaFromPostAction $importPlantillaAction;

    public function __construct(
        ImportHabitFromPostAction $importHabitAction,
        ImportPlantillaFromPostAction $importPlantillaAction
    ) {
        $this->importHabitAction = $importHabitAction;
        $this->importPlantillaAction = $importPlantillaAction;
    }

    public function importHabit(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'post_id' => 'required',
                'dies_setmana' => 'required|array',
                'habit_id' => 'nullable|integer',
            ]);

            $userId = (int) $request->user_id;
            $resultat = $this->importHabitAction->executar($userId, $validated);

            if (!$resultat['success']) {
                $payload = ['message' => $resultat['message']];
                if (isset($resultat['error'])) {
                    $payload['error'] = $resultat['error'];
                }

                return response()->json($payload, $resultat['status'] ?? 422);
            }

            return response()->json([
                'success' => true,
                'habit' => $resultat['habit'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validació',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function importPlantilla(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'post_id' => 'required|integer',
            'habit_ids' => 'required|array',
            'habit_ids.*' => 'integer',
            'plantilla_id' => 'nullable|integer',
        ]);

        $userId = (int) $request->user_id;
        $plantillaId = $request->input('plantilla_id');
        $plantillaIdOverride = null;
        if ($plantillaId !== null) {
            $plantillaIdOverride = (int) $plantillaId;
        }

        $resultat = $this->importPlantillaAction->executar($userId, $validated, $plantillaIdOverride);

        if (!$resultat['success']) {
            $payload = ['message' => $resultat['message']];
            if (isset($resultat['error'])) {
                $payload['error'] = $resultat['error'];
            }

            return response()->json($payload, $resultat['status'] ?? 500);
        }

        return response()->json([
            'success' => true,
            'habits' => $resultat['habits'],
        ]);
    }
}
