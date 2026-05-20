<?php

declare(strict_types=1);

namespace App\Domains\Habits\Actions;

use App\Models\Friendship;
use App\Models\Habit;
use App\Models\UsuariHabit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Importa un hàbit compartit (xat d'amics, etc.) replicant-lo per a l'usuari.
 */
class ImportHabitFromShareAction
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function executar(int $userId, int $habitId, array $input): array
    {
        try {
            $diesIndices = $input['dies_setmana'] ?? [];
            if (!is_array($diesIndices) || $diesIndices === []) {
                return [
                    'success' => false,
                    'message' => 'Has de seleccionar almenys un dia',
                    'status' => 422,
                ];
            }

            $original = Habit::find($habitId);
            if ($original === null) {
                return [
                    'success' => false,
                    'message' => 'Hàbit no trobat',
                    'status' => 404,
                ];
            }

            $propietariId = (int) $original->usuari_id;
            if ($propietariId === $userId) {
                return [
                    'success' => false,
                    'message' => 'Ja és el teu hàbit',
                    'status' => 422,
                ];
            }

            if (!$this->sonAmicsAcceptats($userId, $propietariId)) {
                return [
                    'success' => false,
                    'message' => 'No pots importar aquest hàbit',
                    'status' => 403,
                ];
            }

            $currentCount = UsuariHabit::where('usuari_id', $userId)
                ->where('actiu', true)
                ->count();

            if ($currentCount >= 100) {
                return [
                    'success' => false,
                    'message' => 'Has arribat al límit de 100 hàbits actius.',
                    'status' => 422,
                ];
            }

            $booleanDaysArr = array_fill(0, 7, 'f');
            foreach ($diesIndices as $dayIndex) {
                $dia = (int) $dayIndex;
                if ($dia >= 1 && $dia <= 7) {
                    $booleanDaysArr[$dia - 1] = 't';
                }
            }
            $postgresArray = '{' . implode(',', $booleanDaysArr) . '}';

            $newHabit = $original->replicate();
            $newHabit->usuari_id = $userId;
            $newHabit->dies_setmana = $postgresArray;
            $newHabit->save();

            UsuariHabit::create([
                'usuari_id' => $userId,
                'habit_id' => $newHabit->id,
                'data_inici' => Carbon::now(),
                'actiu' => true,
                'objetiu_vegades_personalitzat' => $newHabit->objectiu_vegades,
            ]);

            return [
                'success' => true,
                'habit' => $newHabit,
            ];
        } catch (\Throwable $e) {
            Log::error('Error important hàbit compartit: ' . $e->getMessage(), [
                'habit_id' => $habitId,
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Error intern del servidor',
                'error' => $e->getMessage(),
                'status' => 500,
            ];
        }
    }

    private function sonAmicsAcceptats(int $usuariId, int $altreId): bool
    {
        return Friendship::where('status', 'accepted')
            ->where(function ($query) use ($usuariId, $altreId) {
                $query->where(function ($q) use ($usuariId, $altreId) {
                    $q->where('requester_id', $usuariId)->where('addressee_id', $altreId);
                })->orWhere(function ($q) use ($usuariId, $altreId) {
                    $q->where('requester_id', $altreId)->where('addressee_id', $usuariId);
                });
            })
            ->exists();
    }
}
