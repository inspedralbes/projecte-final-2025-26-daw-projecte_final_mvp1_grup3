<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Models\SocialPost;
use App\Models\UsuariHabit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Importa un hàbit des d'un post social replicant-lo per a l'usuari.
 */
class ImportHabitFromPostAction
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function executar(int $userId, array $input): array
    {
        try {
            $postId = $input['post_id'];
            $habitId = $input['habit_id'] ?? null;
            $diesIndices = $input['dies_setmana'];

            $post = SocialPost::with('habit')->find($postId);
            if ($post === null) {
                Log::warning('Import habit: post no trobat', ['post_id' => $postId]);

                return [
                    'success' => false,
                    'message' => 'Post no trobat: ' . $postId,
                    'status' => 422,
                ];
            }

            $original = null;
            if ($habitId !== null) {
                $original = Habit::find($habitId);
            } elseif ($post->habit !== null) {
                $original = $post->habit;
            }

            if ($original === null) {
                return [
                    'success' => false,
                    'message' => 'L\'hàbit especificat no existeix o el post no té cap hàbit associat',
                    'status' => 422,
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
                if ($dayIndex >= 1 && $dayIndex <= 7) {
                    $booleanDaysArr[$dayIndex - 1] = 't';
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
            Log::error('Error important hàbit des de post: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error intern del servidor',
                'error' => $e->getMessage(),
                'status' => 500,
            ];
        }
    }
}
