<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\UsuariHabit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OnboardingHabitAssignController extends Controller
{
    public function assign(Request $request): JsonResponse
    {
        $request->validate([
            'habits' => 'required|array|min:1',
            'habits.*.titol' => 'required|string|max:100',
            'habits.*.categoria_id' => 'required|integer',
            'habits.*.dificultat' => 'required|string|in:facil,media,dificil',
            'habits.*.objectiu_vegades' => 'required|integer|min:1',
            'habits.*.senal' => 'nullable|string',
            'habits.*.rutina' => 'nullable|string',
            'habits.*.recompensa' => 'nullable|string',
        ], [
            'habits.required' => 'El camp habits és obligatori.',
            'habits.array' => 'El camp habits ha de ser un array.',
            'habits.min' => 'Cal seleccionar almenys un hàbit.',
            'habits.*.titol.required' => 'El titol és obligatori per a cada hàbit.',
            'habits.*.categoria_id.required' => 'La categoria és obligatòria.',
            'habits.*.dificultat.required' => 'La dificultat és obligatòria.',
            'habits.*.dificultat.in' => 'La dificultat ha de ser: facil, media o dificil.',
            'habits.*.objectiu_vegades.required' => 'L\'objectiu és obligatori.',
        ]);

        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'Usuari no trobat'], 404);
        }

        $habitsData = $request->input('habits');
        $createdHabits = [];

        try {
            foreach ($habitsData as $habitData) {
                $habit = Habit::create([
                    'usuari_id' => $user->id,
                    'categoria_id' => $habitData['categoria_id'],
                    'titol' => $habitData['titol'],
                    'dificultat' => $habitData['dificultat'],
                    'objectiu_vegades' => $habitData['objectiu_vegades'],
                    'frequencia_tipus' => 'diaria',
                    'dies_setmana' => '{t,t,t,t,t,t,t}',
                    'unitat' => 'vegada',
                    'icona' => $habitData['icona'] ?? '📌',
                    'color' => $habitData['color'] ?? '#6C63FF',
                ]);

                UsuariHabit::create([
                    'usuari_id' => $user->id,
                    'habit_id' => $habit->id,
                    'data_inici' => Carbon::now(),
                    'actiu' => true,
                    'objetiu_vegades_personalitzat' => $habitData['objectiu_vegades'],
                ]);

                $createdHabits[] = [
                    'id' => $habit->id,
                    'titol' => $habit->titol,
                    'categoria_id' => $habit->categoria_id,
                    'dificultat' => $habit->dificultat,
                    'objectiu_vegades' => $habit->objectiu_vegades,
                ];
            }

            Log::info('Onboarding habits assigned', [
                'user_id' => $user->id,
                'habits_count' => count($createdHabits),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hàbits assignats correctament',
                'habits' => $createdHabits,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error assigning onboarding habits: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al assignar els hàbits',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
