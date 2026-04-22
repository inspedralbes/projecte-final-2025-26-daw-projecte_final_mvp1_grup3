<?php

namespace Tests\Feature;

use App\Models\Habit;
use App\Models\User;
use App\Models\UsuariHabit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingHabitAssignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\InsertSqlSeeder::class);
    }

    public function test_post_habits_assign_persists_habits_to_habits_table(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders(['user_id' => $user->id])
            ->postJson('/api/habits/assign', [
                'habits' => [
                    [
                        'titol' => 'Meditar cada matí',
                        'categoria_id' => 1,
                        'dificultat' => 'facil',
                        'objectiu_vegades' => 1,
                    ],
                    [
                        'titol' => 'Llegir 30 minuts',
                        'categoria_id' => 2,
                        'dificultat' => 'media',
                        'objectiu_vegades' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'habits' => [
                '*' => ['id', 'titol', 'categoria_id', 'dificultat', 'objectiu_vegades'],
            ],
        ]);

        $this->assertDatabaseHas('habits', [
            'usuari_id' => $user->id,
            'titol' => 'Meditar cada matí',
            'categoria_id' => 1,
            'dificultat' => 'facil',
        ]);

        $this->assertDatabaseHas('habits', [
            'usuari_id' => $user->id,
            'titol' => 'Llegir 30 minuts',
            'categoria_id' => 2,
            'dificultat' => 'media',
        ]);
    }

    public function test_post_habits_assign_creates_usuaris_habits_links(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders(['user_id' => $user->id])
            ->postJson('/api/habits/assign', [
                'habits' => [
                    [
                        'titol' => 'Fer exercici',
                        'categoria_id' => 1,
                        'dificultat' => 'dificil',
                        'objectiu_vegades' => 2,
                    ],
                ],
            ]);

        $response->assertStatus(201);

        $habit = Habit::where('titol', 'Fer exercici')->first();

        $this->assertDatabaseHas('usuaris_habits', [
            'usuari_id' => $user->id,
            'habit_id' => $habit->id,
            'actiu' => true,
            'objetiu_vegades_personalitzat' => 2,
        ]);
    }

    public function test_post_habits_assign_returns_success_with_habit_ids(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders(['user_id' => $user->id])
            ->postJson('/api/habits/assign', [
                'habits' => [
                    [
                        'titol' => 'Nou hàbit',
                        'categoria_id' => 1,
                        'dificultat' => 'facil',
                        'objectiu_vegades' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Hàbits assignats correctament',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'habits' => [
                '*' => ['id', 'titol'],
            ],
        ]);
    }

    public function test_post_habits_assign_returns_validation_errors_for_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders(['user_id' => $user->id])
            ->postJson('/api/habits/assign', [
                'habits' => [
                    [
                        'titol' => '',
                        'categoria_id' => 'invalid',
                        'dificultat' => 'invalid',
                    ],
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'habits.0.titol',
            'habits.0.categoria_id',
            'habits.0.dificultat',
            'habits.0.objectiu_vegades',
        ]);
    }

    public function test_post_habits_assign_returns_401_without_user(): void
    {
        $response = $this->postJson('/api/habits/assign', [
            'habits' => [
                [
                    'titol' => 'Test',
                    'categoria_id' => 1,
                    'dificultat' => 'facil',
                    'objectiu_vegades' => 1,
                ],
            ],
        ]);

        $response->assertStatus(401);
    }

    public function test_post_habits_assign_accepts_empty_selection(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders(['user_id' => $user->id])
            ->postJson('/api/habits/assign', [
                'habits' => [],
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'habits' => [],
        ]);

        $this->assertDatabaseMissing('habits', [
            'usuari_id' => $user->id,
        ]);
    }
}
