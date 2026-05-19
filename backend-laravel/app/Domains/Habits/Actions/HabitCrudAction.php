<?php

declare(strict_types=1);

namespace App\Domains\Habits\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Habits\Support\HabitDataMapper;
use App\Domains\Habits\Support\HabitMetadataNormalizer;
use App\Models\Habit;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * CRUD d'hàbits per a l'usuari propietari.
 */
class HabitCrudAction
{
    private HabitDataMapper $dataMapper;

    private HabitMetadataNormalizer $metadataNormalizer;

    public function __construct(
        HabitDataMapper $dataMapper,
        HabitMetadataNormalizer $metadataNormalizer
    ) {
        $this->dataMapper = $dataMapper;
        $this->metadataNormalizer = $metadataNormalizer;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $habitData
     */
    public function crearHabit(int $usuariId, array $habitData): ?Habit
    {
        if (!$this->metadataNormalizer->validarShapeMetadata($habitData)) {
            return null;
        }

        $dades = $this->dataMapper->filtrarDadesHabit($habitData);
        $dades['usuari_id'] = $usuariId;

        return Habit::create($dades);
    }

    /**
     * @param  array<string, mixed>  $habitData
     */
    public function actualitzarHabit(int $usuariId, int $habitId, array $habitData): ?Habit
    {
        if (!$this->metadataNormalizer->validarShapeMetadata($habitData)) {
            return null;
        }

        $habit = Habit::find($habitId);

        if (!$habit || (int) $habit->usuari_id !== $usuariId) {
            return null;
        }

        $dades = $this->dataMapper->filtrarDadesHabit($habitData);

        if (!empty($dades)) {
            $habit->update($dades);
        }

        return $habit->fresh();
    }

    public function eliminarHabit(int $usuariId, int $habitId): ?Habit
    {
        $habit = Habit::find($habitId);

        if (!$habit || (int) $habit->usuari_id !== $usuariId) {
            return null;
        }

        $habit->delete();

        return $habit;
    }
}
