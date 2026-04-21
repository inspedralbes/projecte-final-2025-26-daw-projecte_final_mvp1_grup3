<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use App\Models\UsuariHabit;
use App\Models\Habit;
use App\Services\HabitService;
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
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:social_posts,id',
            'dies_setmana' => 'required|array|size:7',
            'dies_setmana.*' => 'boolean',
        ]);

        $post = SocialPost::with('habit')->findOrFail($validated['post_id']);

        if (!$post->habit) {
            return response()->json(['message' => 'El post no té cap hàbit associat'], 422);
        }

        $userId = $request->user_id;
        $currentCount = UsuariHabit::where('usuari_id', $userId)
            ->where('actiu', true)
            ->count();

        if ($currentCount >= 20) {
            return response()->json([
                'message' => 'Has arribat al límit de 20 hàbits actius. Elimina alguns hàbits abans d importar.',
            ], 422);
        }

        $original = $post->habit;
        $newHabit = $original->replicate();
        $newHabit->usuari_id = $userId;
        $newHabit->dies_setmana = $validated['dies_setmana'];
        $newHabit->save();

        UsuariHabit::create([
            'usuari_id' => $userId,
            'habit_id' => $newHabit->id,
            'data_inici' => Carbon::now(),
            'actiu' => true,
            'objetiu_vegades_personalitzat' => $newHabit->objectiu_vegades,
        ]);

        return response()->json([
            'success' => true,
            'habit' => $newHabit,
        ]);
    }

    public function importPlantilla(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'post_id' => 'required|integer|exists:social_posts,id',
            'habit_ids' => 'required|array|min:1',
            'habit_ids.*' => 'integer|exists:habits,id',
        ]);

        $post = SocialPost::with('plantilla.habits')->findOrFail($validated['post_id']);

        if (!$post->plantilla) {
            return response()->json(['message' => 'El post no té cap plantilla associada'], 422);
        }

        $userId = $request->user_id;

        $result = $this->habitService->exportarHabitsDePlantilla(
            $userId,
            $post->plantilla_id,
            $validated['habit_ids']
        );

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'habits' => $result['habits'],
        ]);
    }
}