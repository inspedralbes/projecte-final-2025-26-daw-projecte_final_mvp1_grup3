<?php

declare(strict_types=1);

namespace App\Domains\Habits\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Models\UsuariHabit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Exporta hàbits seleccionats d'una plantilla cap a l'inventari d'un usuari.
 */
class ExportHabitsFromPlantillaAction
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<int>  $habitsSeleccionats
     * @return array<string, mixed>
     */
    public function executar(int $usuariId, int $plantillaId, array $habitsSeleccionats): array
    {
        try {
            $nousHabits = [];

            $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');

            $titolsExistents = Habit::where('usuari_id', $usuariId)
                ->orWhereIn('id', $habitIdsAssignats)
                ->pluck('titol')
                ->toArray();

            $titolsNormalitzats = [];
            foreach ($titolsExistents as $titol) {
                $titolsNormalitzats[] = strtolower(trim((string) $titol));
            }

            DB::transaction(function () use ($usuariId, $habitsSeleccionats, $titolsNormalitzats, &$nousHabits) {
                $originals = Habit::whereIn('id', $habitsSeleccionats)->get();

                foreach ($originals as $original) {
                    $titolNou = strtolower(trim((string) $original->titol));

                    if (in_array($titolNou, $titolsNormalitzats, true)) {
                        continue;
                    }

                    $nou = $original->replicate();
                    $nou->usuari_id = $usuariId;
                    $nou->save();

                    UsuariHabit::create([
                        'usuari_id' => $usuariId,
                        'habit_id' => $nou->id,
                        'data_inici' => Carbon::now(),
                        'actiu' => true,
                        'objetiu_vegades_personalitzat' => $nou->objectiu_vegades,
                    ]);

                    $nousHabits[] = $nou->toArray();
                    $titolsNormalitzats[] = $titolNou;
                }
            });

            return [
                'success' => true,
                'habits' => $nousHabits,
            ];
        } catch (\Throwable $e) {
            Log::error('Error exportant hàbits: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error al exportar hàbits: ' . $e->getMessage(),
            ];
        }
    }
}
