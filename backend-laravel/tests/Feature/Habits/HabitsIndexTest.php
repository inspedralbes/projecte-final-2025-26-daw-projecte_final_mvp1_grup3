<?php


/**
 * Capa Laravel: HabitsIndexTest.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace Tests\Feature\Habits;

//================================ NAMESPACES / IMPORTS ============

use App\Models\User;
use Database\Factories\HabitFactory;
use Tests\TestCase;

//================================ MÈTODES / FUNCIONS ===========

class HabitsIndexTest extends TestCase
{
    public function test_habits_requires_token(): void
    {
        $response = $this->getJson('/api/habits');

        $response
            ->assertStatus(401)
            ->assertJsonPath('message', 'Token invàlid o expirat');
    }

    public function test_habits_returns_empty_data_for_user_without_habits(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenForUser($user->id, $user->email);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/habits');

        $response
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_habits_returns_user_habits_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = $this->tokenForUser($user->id, $user->email);

        HabitFactory::new()->create([
            'usuari_id' => $user->id,
            'titol' => 'Beure aigua',
        ]);
        HabitFactory::new()->create([
            'usuari_id' => $user->id,
            'titol' => 'Caminar',
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/habits');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }
}
