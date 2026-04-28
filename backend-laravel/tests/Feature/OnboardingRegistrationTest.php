<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_profile_is_created_with_default_values_on_registration(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nom' => 'Test User',
            'email' => 'test@example.com',
            'contrasenya' => 'password123',
            'contrasenya_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertCookie('loopy_token');

        $this->assertDatabaseHas('usuaris', [
            'nom' => 'Test User',
            'email' => 'test@example.com',
            'nivell' => 1,
            'xp_total' => 0,
            'xp_actual_nivel' => 0,
            'xp_objetivo_nivel' => 1000,
            'monedes' => 0,
            'missio_completada' => false,
        ]);
    }

    public function test_ratxa_is_created_on_registration(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'nom' => 'Test User',
            'email' => 'test@example.com',
            'contrasenya' => 'password123',
            'contrasenya_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'test@example.com')->first();

        $this->assertDatabaseHas('ratxes', [
            'usuari_id' => $user->id,
            'ratxa_actual' => 0,
            'ratxa_maxima' => 0,
        ]);
    }
}
