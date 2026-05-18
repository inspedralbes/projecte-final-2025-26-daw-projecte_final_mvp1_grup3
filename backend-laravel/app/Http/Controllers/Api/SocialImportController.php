<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use App\Models\UsuariHabit;
use App\Models\Habit;
use App\Services\HabitService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SocialImportController extends Controller
{
    protected HabitService $habitService;

    public function __construct(HabitService $habitService)
    {
        $this->habitService = $habitService;
    }

    public function importHabit(Request $request): JsonResponse
    {
        error_log('--- IMPORT HABIT ---');
        error_log('Input: ' . json_encode($request->all()));
        error_log('User: ' . ($request->user_id ?? 'NULL'));

        try {
            $request->validate([
                'post_id' => 'required',
                'dies_setmana' => 'required|array',
                'habit_id' => 'nullable|integer',
            ]);

            $postId = $request->input('post_id');
            $habitId = $request->input('habit_id');
            $diesIndices = $request->input('dies_setmana');

            $post = SocialPost::with('habit')->find($postId);
            if (!$post) {
                error_log('Import Error: Post not found ' . $postId);
                return response()->json(['message' => 'Post no trobat: ' . $postId], 422);
            }

            $original = null;
            if ($habitId) {
                $original = Habit::find($habitId);
            } elseif ($post->habit) {
                $original = $post->habit;
            }

            if (!$original) {
                error_log('Import Error: Habit not found for post ' . $postId);
                return response()->json(['message' => 'L\'hàbit especificat no existeix o el post no té cap hàbit associat'], 422);
            }

            $userId = $request->user_id;

            $currentCount = UsuariHabit::where('usuari_id', $userId)
                ->where('actiu', true)
                ->count();

            if ($currentCount >= 100) {
                error_log('Import Error: Limit of 100 habits reached for user ' . $userId);
                return response()->json([
                    'message' => 'Has arribat al límit de 100 hàbits actius.',
                ], 422);
            }

            $booleanDaysArr = array_fill(0, 7, 'f');
            foreach ($diesIndices as $dayIndex) {
                if ($dayIndex >= 1 && $dayIndex <= 7) {
                    $booleanDaysArr[$dayIndex - 1] = 't';
                }
            }
            $postgresArray = '{' . implode(',', $booleanDaysArr) . '}';

            $newHabit = $original->replicate();
            $newHabit->usuari_id = $userId;
            $newHabit->dies_setmana = $postgresArray;
            $newHabit->save();

            error_log('Habit replicated and saved. ID: ' . $newHabit->id);

            UsuariHabit::create([
                'usuari_id' => $userId,
                'habit_id' => $newHabit->id,
                'data_inici' => Carbon::now(),
                'actiu' => true,
                'objetiu_vegades_personalitzat' => $newHabit->objectiu_vegades,
            ]);

            error_log('Pivot record created. success!');

            return response()->json([
                'success' => true,
                'habit' => $newHabit,
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            error_log('Validation Error in importHabit: ' . json_encode($e->errors()));
            return response()->json([
                'message' => 'Error de validació',
                'errors' => $e->errors()
            ], 422);
        }
        catch (\Exception $e) {
            error_log('Exception in importHabit: ' . $e->getMessage());
            error_log($e->getTraceAsString());
            return response()->json([
                'message' => 'Error intern del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function importPlantilla(Request $request): JsonResponse
    {
        error_log('--- IMPORT PLANTILLA START ---');
        error_log('Input: ' . json_encode($request->all()));
        error_log('User: ' . ($request->user_id ?? 'NULL'));

        try {
            $validated = $request->validate([
                'post_id' => 'required|integer',
                'habit_ids' => 'required|array',
                'habit_ids.*' => 'integer',
                'plantilla_id' => 'nullable|integer',
            ]);

            $postId = $validated['post_id'];
            $plantillaId = $request->input('plantilla_id');
            $habitIds = $validated['habit_ids'];

            $post = SocialPost::with('plantilla.habits')->findOrFail($postId);

            $targetPlantillaId = $plantillaId ?? $post->plantilla_id;

            if (!$targetPlantillaId) {
                error_log('Post has no template: ' . $postId);
                return response()->json(['message' => 'El post no té cap plantilla associada'], 422);
            }

            $userId = $request->user_id;

            $result = $this->habitService->exportarHabitsDePlantilla(
                $userId,
                $targetPlantillaId,
                $habitIds
            );

            error_log('Export success! Habits imported: ' . count($result['habits']));

            return response()->json([
                'success' => true,
                'habits' => $result['habits'],
            ]);
        }
        catch (\Exception $e) {
            error_log('Exception in importPlantilla: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error en importar plantilla',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}