<?php


/**
 * Capa Laravel: UserFactory.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace Database\Factories;

//================================ NAMESPACES / IMPORTS ============

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
//================================ MÈTODES / FUNCIONS ===========

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'contrasenya_hash' => Hash::make('secret123'),
            'nivell' => 1,
            'xp_total' => 0,
            'xp_actual_nivel' => 0,
            'xp_objetivo_nivel' => 1000,
            'monedes' => 0,
            'prohibit' => false,
            'missio_completada' => false,
        ];
    }
}
