<?php

declare(strict_types=1);

namespace App\Domains\Onboarding\Actions;

use App\Domains\Onboarding\Support\OnboardingHabitCategoryDefaults;
use App\Models\Habit;
use App\Models\User;
use App\Models\UsuariHabit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Crea hàbits inicials des de la selecció d'onboarding.
 */
class AssignOnboardingHabitsAction
{
    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function executar(Request $request): array
    {
        $this->normalitzarDificultatAlRequest($request);

        $request->validate([
            'habits' => 'required|array',
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
            'habits.*.titol.required' => 'El titol és obligatori per a cada hàbit.',
            'habits.*.categoria_id.required' => 'La categoria és obligatòria.',
            'habits.*.dificultat.required' => 'La dificultat és obligatòria.',
            'habits.*.dificultat.in' => 'La dificultat ha de ser: facil, media o dificil.',
            'habits.*.objectiu_vegades.required' => 'L\'objectiu és obligatori.',
        ]);

        $userId = (int) $request->input('user_id');
        if ($userId < 1) {
            return ['status' => 401, 'body' => ['message' => 'Unauthorized']];
        }

        $user = User::find($userId);
        if (!$user) {
            return ['status' => 404, 'body' => ['message' => 'Usuari no trobat']];
        }

        $habitsData = $request->input('habits');
        if (! is_array($habitsData)) {
            return ['status' => 422, 'body' => ['message' => 'Format habits invàlid']];
        }

        $createdHabits = [];

        try {
            if (count($habitsData) === 0) {
                Log::info('Onboarding habits assign with empty selection', ['user_id' => $user->id]);

                return [
                    'status' => 200,
                    'body' => [
                        'success' => true,
                        'message' => 'Onboarding completat sense hàbits',
                        'habits' => [],
                    ],
                ];
            }

            DB::transaction(function () use ($habitsData, $user, &$createdHabits) {
                foreach ($habitsData as $habitData) {
                    if (! is_array($habitData)) {
                        continue;
                    }
                    $catId = (int) $habitData['categoria_id'];
                    $icona = $habitData['icona'] ?? OnboardingHabitCategoryDefaults::icona($catId);
                    $color = $habitData['color'] ?? OnboardingHabitCategoryDefaults::color($catId);

                    $habit = Habit::create([
                        'usuari_id' => $user->id,
                        'categoria_id' => $catId,
                        'titol' => $habitData['titol'],
                        'dificultat' => $habitData['dificultat'],
                        'objectiu_vegades' => $habitData['objectiu_vegades'],
                        'frequencia_tipus' => 'diaria',
                        'dies_setmana' => '{t,t,t,t,t,t,t}',
                        'unitat' => 'vegades',
                        'icona' => $icona,
                        'color' => $color,
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
                        'usuari_id' => $user->id,
                        'titol' => $habit->titol,
                        'categoria_id' => $habit->categoria_id,
                        'dificultat' => $habit->dificultat,
                        'objectiu_vegades' => $habit->objectiu_vegades,
                        'frequencia_tipus' => $habit->frequencia_tipus,
                        'dies_setmana' => [true, true, true, true, true, true, true],
                        'unitat' => $habit->unitat,
                        'icona' => $habit->icona,
                        'color' => $habit->color,
                        'moment_dia' => 'tot_dia',
                        'recordatori' => '',
                    ];
                }
            });

            Log::info('Onboarding habits assigned', [
                'user_id' => $user->id,
                'habits_count' => count($createdHabits),
            ]);

            return [
                'status' => 201,
                'body' => [
                    'success' => true,
                    'message' => 'Hàbits assignats correctament',
                    'habits' => $createdHabits,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Error assigning onboarding habits: ' . $e->getMessage());

            return [
                'status' => 500,
                'body' => [
                    'message' => 'Error al assignar els hàbits',
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    private function normalitzarDificultatAlRequest(Request $request): void
    {
        $habits = $request->input('habits');
        if (! is_array($habits)) {
            return;
        }
        foreach ($habits as $i => $fila) {
            if (! is_array($fila)) {
                continue;
            }
            if (isset($fila['dificultat']) && $fila['dificultat'] === 'mitjana') {
                $habits[$i]['dificultat'] = 'media';
            }
        }
        $request->merge(['habits' => $habits]);
    }
}
