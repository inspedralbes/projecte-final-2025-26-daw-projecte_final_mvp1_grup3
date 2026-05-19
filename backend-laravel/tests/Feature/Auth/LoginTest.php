<?php


/**
 * Capa Laravel: LoginTest.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace Tests\Feature\Auth;

//================================ NAMESPACES / IMPORTS ============

use App\Models\User;
use Tests\TestCase;

//================================ MÈTODES / FUNCIONS ===========

class LoginTest extends TestCase
{
    public function test_login_ok_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@login.test',
            'contrasenya_hash' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@login.test',
            'contrasenya' => 'secret123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'role',
                'user' => ['id', 'nom', 'email'],
            ])
            ->assertJsonPath('role', 'user')
            ->assertJsonPath('user.email', 'user@login.test');
    }

    public function test_login_returns_401_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'user@login.test',
            'contrasenya_hash' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@login.test',
            'contrasenya' => 'incorrecta',
        ]);

        $response
            ->assertStatus(401)
            ->assertJsonPath('message', 'Credencials incorrectes');
    }

    public function test_login_returns_422_when_payload_is_empty(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'contrasenya']);
    }
}
