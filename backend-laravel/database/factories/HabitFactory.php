<?php

namespace Database\Factories;

use App\Models\Habit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Habit>
 */
class HabitFactory extends Factory
{
    protected $model = Habit::class;

    public function definition(): array
    {
        return [
            'usuari_id' => null,
            'plantilla_id' => null,
            'categoria_id' => null,
            'titol' => fake()->words(2, true),
            'dificultat' => 'mitjana',
            'frequencia_tipus' => 'setmanal',
            // Null per passar el filtre "whereNull(dies_setmana)" del controlador.
            'dies_setmana' => null,
            'objectiu_vegades' => 1,
            'unitat' => 'vegades',
            'icona' => 'check',
            'color' => '#22C55E',
            'moment_dia' => 'tot_dia',
        ];
    }
}
