<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class OnboardingRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('GOOGLE_FRONTEND_REDIRECT=http://localhost:3000/auth/google/redirect');
    }

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

    public function test_google_callback_marks_onboarding_for_new_google_user(): void
    {
        $googleUser = Mockery::mock(SocialiteUserContract::class);
        $googleUser->shouldReceive('getId')->andReturn('google-new-123');
        $googleUser->shouldReceive('getEmail')->andReturn('new-google-user@example.com');
        $googleUser->shouldReceive('getName')->andReturn('Nou Usuari Google');
        $googleUser->shouldReceive('getNickname')->andReturnNull();

        $googleDriver = Mockery::mock();
        $googleDriver->shouldReceive('stateless')->once()->andReturnSelf();
        $googleDriver->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($googleDriver);

        $response = $this->get('/api/auth/google/callback');

        $response->assertStatus(302);
        $response->assertCookie('loopy_token');
        $response->assertCookie('loopy_role', 'user');
        $this->assertStringContainsString(
            'http://localhost:3000/auth/google/redirect?token=',
            (string) $response->headers->get('Location')
        );
        $this->assertStringContainsString(
            '&onboarding=1',
            (string) $response->headers->get('Location')
        );
        $this->assertDatabaseHas('usuaris', [
            'email' => 'new-google-user@example.com',
            'google_id' => 'google-new-123',
        ]);
    }

    public function test_google_callback_skips_onboarding_for_existing_email_user(): void
    {
        $registerResponse = $this->postJson('/api/auth/register', [
            'nom' => 'Usuari Antic',
            'email' => 'existing-google@example.com',
            'contrasenya' => 'password123',
            'contrasenya_confirmation' => 'password123',
        ]);
        $registerResponse->assertStatus(201);

        $googleUser = Mockery::mock(SocialiteUserContract::class);
        $googleUser->shouldReceive('getId')->andReturn('google-existing-456');
        $googleUser->shouldReceive('getEmail')->andReturn('existing-google@example.com');
        $googleUser->shouldReceive('getName')->andReturn('Usuari Google Existent');
        $googleUser->shouldReceive('getNickname')->andReturnNull();

        $googleDriver = Mockery::mock();
        $googleDriver->shouldReceive('stateless')->once()->andReturnSelf();
        $googleDriver->shouldReceive('user')->once()->andReturn($googleUser);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($googleDriver);

        $response = $this->get('/api/auth/google/callback');

        $response->assertStatus(302);
        $response->assertCookie('loopy_token');
        $response->assertCookie('loopy_role', 'user');
        $this->assertStringContainsString(
            'http://localhost:3000/auth/google/redirect?token=',
            (string) $response->headers->get('Location')
        );
        $this->assertStringContainsString(
            '&onboarding=0',
            (string) $response->headers->get('Location')
        );
        $this->assertDatabaseHas('usuaris', [
            'email' => 'existing-google@example.com',
            'google_id' => 'google-existing-456',
        ]);
    }
}
