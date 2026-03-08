<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Resources\HabitResource;
use App\Http\Resources\LogroResource;
use App\Models\Habit;
use App\Models\RegistreActivitat;
use App\Models\UsuariHabit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Servei per obtenir les dades consolidades de la home de l'usuari.
 * Centralitza game_state, hàbits, progrés i logros en un sol punt.
 */
class HomeDataService
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Obté totes les dades necessàries per a la pantalla home.
     *
     * A. Estat de gamificació (GamificationService).
     * B. Hàbits del dia (Habit + UsuariHabit).
     * C. Progrés diari (RegistreActivitat).
     * D. Logros (LogroService).
     *
     * @param  int  $usuariId
     * @param  Request  $request  Necessari per HabitResource i LogroResource (user_id).
     * @return array<string, mixed>
     */
    public function obtenirDadesHome(int $usuariId, Request $request): array
    {
        $gamificationService = app(GamificationService::class);
        $logroService = app(LogroService::class);

        // A. Estat de gamificació
        $gameState = $gamificationService->obtenirEstatGamificacio($usuariId);

        // B. Hàbits del dia
        $habits = $this->obtenirHabitsDelDia($usuariId);
        $habitsResolved = HabitResource::collection($habits)->resolve($request);
        $habitsArray = isset($habitsResolved['data']) ? $habitsResolved['data'] : $habitsResolved;

        // C. Progrés diari
        $habitProgress = $this->obtenirProgresDelDia($usuariId, $habits);

        // D. Logros
        $logros = $logroService->llistarTotsElsLogros($usuariId);
        $logrosResolved = LogroResource::collection($logros)->resolve($request);
        $logrosArray = isset($logrosResolved['data']) ? $logrosResolved['data'] : $logrosResolved;

        return [
            'game_state' => $gameState,
            'habits' => $habitsArray,
            'habit_progress' => $habitProgress,
            'logros' => $logrosArray,
        ];
    }

    /**
     * Obté els hàbits del dia de l'usuari (propis i assignats).
     *
     * @param  int  $usuariId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function obtenirHabitsDelDia(int $usuariId)
    {
        $diaIndex = (int) now()->dayOfWeekIso;
        $habitIdsAssignats = UsuariHabit::where('usuari_id', $usuariId)->pluck('habit_id');
        $query = Habit::where('usuari_id', $usuariId)
            ->orWhereIn('id', $habitIdsAssignats);
        $query->where(function ($q) use ($diaIndex) {
            $q->whereNull('dies_setmana')
                ->orWhereRaw('dies_setmana[' . $diaIndex . '] = true');
        });

        return $query->get();
    }

    /**
     * Obté el progrés diari per als hàbits donats.
     *
     * @param  int  $usuariId
     * @param  \Illuminate\Database\Eloquent\Collection  $habits
     * @return array<int, array<string, mixed>>
     */
    private function obtenirProgresDelDia(int $usuariId, $habits): array
    {
        $habitIds = $habits->pluck('id')->toArray();
        if (empty($habitIds)) {
            return [];
        }

        $avui = Carbon::today();

        $progres = DB::table('registre_activitat')
            ->select('habit_id', DB::raw('COALESCE(SUM(valor), 0) as progress'))
            ->whereIn('habit_id', $habitIds)
            ->whereDate('data', $avui)
            ->groupBy('habit_id')
            ->get()
            ->keyBy('habit_id');

        $completats = RegistreActivitat::whereIn('habit_id', $habitIds)
            ->whereDate('data', $avui)
            ->where('acabado', true)
            ->pluck('habit_id')
            ->toArray();

        $resultat = [];
        foreach ($habits as $habit) {
            $progress = 0;
            if (isset($progres[$habit->id])) {
                $progress = (int) $progres[$habit->id]->progress;
            }
            $resultat[] = [
                'habit_id' => $habit->id,
                'progress' => $progress,
                'completed_today' => in_array($habit->id, $completats),
                'objectiu_vegades' => (int) $habit->objectiu_vegades,
                'titol' => $habit->titol,
                'unitat' => $habit->unitat,
            ];
        }

        return $resultat;
    }
}
