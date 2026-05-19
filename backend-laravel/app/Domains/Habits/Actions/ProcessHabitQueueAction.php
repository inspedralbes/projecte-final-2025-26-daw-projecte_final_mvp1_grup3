<?php

declare(strict_types=1);

namespace App\Domains\Habits\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Habits\Support\HabitQueueFeedbackAssembler;
use App\Models\Habit;
use App\Services\MissionService;
use Carbon\Carbon;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Executa una acció de la cua habits_queue i retorna l'estat per al feedback.
 */
class ProcessHabitQueueAction
{
    private HabitCrudAction $crudAction;

    private UpdateHabitProgressAction $progressAction;

    private CompleteHabitAction $completeAction;

    private FocusUpdateAction $focusAction;

    private ExportHabitsFromPlantillaAction $exportAction;

    private MissionService $missionService;

    private HabitQueueFeedbackAssembler $feedbackAssembler;

    public function __construct(
        HabitCrudAction $crudAction,
        UpdateHabitProgressAction $progressAction,
        CompleteHabitAction $completeAction,
        FocusUpdateAction $focusAction,
        ExportHabitsFromPlantillaAction $exportAction,
        MissionService $missionService,
        HabitQueueFeedbackAssembler $feedbackAssembler
    ) {
        $this->crudAction = $crudAction;
        $this->progressAction = $progressAction;
        $this->completeAction = $completeAction;
        $this->focusAction = $focusAction;
        $this->exportAction = $exportAction;
        $this->missionService = $missionService;
        $this->feedbackAssembler = $feedbackAssembler;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $dades
     */
    public function executar(array $dades): void
    {
        if (isset($dades['action'])) {
            $accio = strtoupper((string) $dades['action']);
        } else {
            $accio = '';
        }

        if (!isset($dades['user_id']) || (int) $dades['user_id'] < 1) {
            return;
        }
        $usuariId = (int) $dades['user_id'];
        $habitId = isset($dades['habit_id']) ? (int) $dades['habit_id'] : 0;
        $habitData = isset($dades['habit_data']) && is_array($dades['habit_data']) ? $dades['habit_data'] : [];

        $estat = $this->processarAccio($accio, $usuariId, $habitId, $habitData, $dades);
        $estat['accio'] = $accio;
        $estat['usuari_id'] = $usuariId;

        $this->feedbackAssembler->publicar($estat);
    }

    //================================ RUTES / LOGICA PRIVADA ========

    /**
     * @param  array<string, mixed>  $dades
     * @return array<string, mixed>
     */
    private function processarAccio(
        string $accio,
        int $usuariId,
        int $habitId,
        array $habitData,
        array $dades
    ): array {
        $estat = [
            'success' => false,
            'habit_model' => null,
            'xp_update' => null,
            'mission_completed' => null,
            'progress' => null,
            'completed_today' => null,
            'message' => null,
            'level_up' => null,
            'exported_habits' => null,
        ];

        if ($accio === 'CREATE') {
            $estat['habit_model'] = $this->crudAction->crearHabit($usuariId, $habitData);
            $estat['success'] = $estat['habit_model'] !== null;

            return $estat;
        }

        if ($accio === 'UPDATE') {
            $estat['habit_model'] = $this->crudAction->actualitzarHabit($usuariId, $habitId, $habitData);
            $estat['success'] = $estat['habit_model'] !== null;

            return $estat;
        }

        if ($accio === 'DELETE') {
            $estat['habit_model'] = $this->crudAction->eliminarHabit($usuariId, $habitId);
            $estat['success'] = $estat['habit_model'] !== null;

            return $estat;
        }

        if ($accio === 'PROGRESS') {
            return $this->processarProgres($usuariId, $habitId, $dades, $estat);
        }

        if ($accio === 'FOCUS_UPDATE') {
            return $this->processarFocus($usuariId, $habitId, $dades, $estat);
        }

        if ($accio === 'COMPLETE' || $accio === 'TOGGLE') {
            return $this->processarComplete($usuariId, $habitId, $dades, $estat);
        }

        if ($accio === 'EXPORT_HABITS') {
            $plantillaId = isset($dades['plantilla_id']) ? (int) $dades['plantilla_id'] : 0;
            $seleccionats = isset($dades['selected_habits']) ? $dades['selected_habits'] : [];
            $resultat = $this->exportAction->executar($usuariId, $plantillaId, $seleccionats);
            $estat['success'] = $resultat['success'];
            if ($resultat['success']) {
                $estat['exported_habits'] = $resultat['habits'];
            } else {
                $estat['message'] = $resultat['message'] ?? 'No s\'ha pogut exportar.';
            }

            return $estat;
        }

        throw new \InvalidArgumentException('Acció d\'hàbits no reconeguda.');
    }

    /**
     * @param  array<string, mixed>  $dades
     * @param  array<string, mixed>  $estat
     * @return array<string, mixed>
     */
    private function processarProgres(int $usuariId, int $habitId, array $dades, array $estat): array
    {
        $delta = isset($dades['valor']) ? (int) $dades['valor'] : 1;
        $resultat = $this->progressAction->processarProgresHabit($habitId, $usuariId, $delta);
        $estat['habit_model'] = Habit::find($habitId);

        if ($resultat === null) {
            $estat['message'] = 'No s\'ha pogut actualitzar el progrés.';

            return $estat;
        }

        $estat['success'] = true;
        $estat['progress'] = $resultat['progress'];
        $estat['completed_today'] = $resultat['completed_today'];
        if (isset($resultat['xp_update'])) {
            $estat['xp_update'] = $resultat['xp_update'];
        }
        if ($resultat['completed_today'] === true) {
            $estat = $this->afegirMissio($usuariId, $habitId, Carbon::now(), $estat);
        }

        return $estat;
    }

    /**
     * @param  array<string, mixed>  $dades
     * @param  array<string, mixed>  $estat
     * @return array<string, mixed>
     */
    private function processarFocus(int $usuariId, int $habitId, array $dades, array $estat): array
    {
        $focusData = $this->construirDadesFocus($dades, $habitId, $usuariId);
        $resultat = $this->focusAction->executar($focusData);
        $estat['habit_model'] = Habit::find($habitId);
        $estat['success'] = (bool) ($resultat['success'] ?? false);

        if (isset($resultat['progress'])) {
            $estat['progress'] = (int) $resultat['progress'];
        }
        if (isset($resultat['completed_today'])) {
            $estat['completed_today'] = (bool) $resultat['completed_today'];
        }
        if (isset($resultat['xp_update'])) {
            $estat['xp_update'] = $resultat['xp_update'];
        }
        if (isset($resultat['level_up'])) {
            $estat['level_up'] = $resultat['level_up'];
        }
        if ($estat['success'] !== true) {
            $estat['message'] = $resultat['message'] ?? 'No s\'ha pogut processar la sessió de focus.';
        }
        if ($estat['success'] === true && ($resultat['completed_today'] ?? false) === true) {
            $dataMissio = isset($dades['data']) ? Carbon::parse($dades['data']) : Carbon::now();
            $estat = $this->afegirMissio($usuariId, $habitId, $dataMissio, $estat);
        }

        return $estat;
    }

    /**
     * @param  array<string, mixed>  $dades
     * @param  array<string, mixed>  $estat
     * @return array<string, mixed>
     */
    private function processarComplete(int $usuariId, int $habitId, array $dades, array $estat): array
    {
        $resultat = $this->completeAction->executar([
            'habit_id' => $habitId,
            'user_id' => $usuariId,
            'data' => isset($dades['data']) ? $dades['data'] : null,
        ]);
        $estat['habit_model'] = Habit::find($habitId);

        if (($resultat['success'] ?? false) !== true) {
            $estat['message'] = $resultat['message'] ?? 'No s\'ha pogut completar l\'hàbit.';

            return $estat;
        }

        $estat['success'] = true;
        if (isset($resultat['xp_update'])) {
            $estat['xp_update'] = $resultat['xp_update'];
        }
        if (isset($resultat['completed_today'])) {
            $estat['completed_today'] = $resultat['completed_today'];
        }
        if (isset($resultat['level_up'])) {
            $estat['level_up'] = $resultat['level_up'];
        }

        $dataMissio = isset($dades['data']) ? Carbon::parse($dades['data']) : Carbon::now();

        return $this->afegirMissio($usuariId, $habitId, $dataMissio, $estat);
    }

    /**
     * @param  array<string, mixed>  $estat
     * @return array<string, mixed>
     */
    private function afegirMissio(int $usuariId, int $habitId, Carbon $data, array $estat): array
    {
        $resultatMissio = $this->missionService->comprovarMissioCompletada($usuariId, $habitId, $data);
        if ($resultatMissio === null || ($resultatMissio['completada'] ?? false) !== true) {
            return $estat;
        }

        $missionCompleted = ['success' => true];
        if (isset($resultatMissio['missio_objectiu'])) {
            $missionCompleted['missio_objectiu'] = (int) $resultatMissio['missio_objectiu'];
        }
        if (isset($resultatMissio['xp_update']) && is_array($resultatMissio['xp_update'])) {
            $xpUpdate = $estat['xp_update'];
            if (is_array($xpUpdate)) {
                $missionCompleted['xp_update'] = array_merge($xpUpdate, $resultatMissio['xp_update']);
            } else {
                $missionCompleted['xp_update'] = $resultatMissio['xp_update'];
            }
            $estat['xp_update'] = $missionCompleted['xp_update'];
        }
        $estat['mission_completed'] = $missionCompleted;

        return $estat;
    }

    /**
     * @param  array<string, mixed>  $dades
     * @return array<string, mixed>
     */
    private function construirDadesFocus(array $dades, int $habitId, int $usuariId): array
    {
        $mode = null;
        if (isset($dades['focus_mode'])) {
            $mode = (string) $dades['focus_mode'];
        } elseif (isset($dades['mode'])) {
            $mode = (string) $dades['mode'];
        }

        $minutes = 0;
        if (isset($dades['focus_minutes'])) {
            $minutes = (int) $dades['focus_minutes'];
        } elseif (isset($dades['minutes'])) {
            $minutes = (int) $dades['minutes'];
        }

        $event = 'update';
        if (isset($dades['focus_event'])) {
            $event = (string) $dades['focus_event'];
        } elseif (isset($dades['event'])) {
            $event = (string) $dades['event'];
        }

        return [
            'habit_id' => $habitId,
            'user_id' => $usuariId,
            'mode' => $mode,
            'minutes' => $minutes,
            'event' => $event,
            'data' => isset($dades['data']) ? $dades['data'] : null,
        ];
    }
}
