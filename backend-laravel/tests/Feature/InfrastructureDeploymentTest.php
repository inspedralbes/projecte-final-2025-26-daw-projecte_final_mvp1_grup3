<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Clan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Redis;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class InfrastructureDeploymentTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Aseguramos que el entorno es testing
        $this->app['config']->set('database.default', 'pgsql');
        $this->app['config']->set('database.connections.pgsql.database', 'loopy_test');
    }

    /**
     * Test 1: Comprobar ruta de Social Feed.
     */
    public function test_social_feed_route()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson('/api/social/posts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'content',
                    'user_id',
                    'user' => ['nom', 'monstre_tipus', 'nivell']
                ]
            ]
        ]);
    }

    /**
     * Test 2: Comprobar ruta de Detalles de Clan.
     */
    public function test_clan_details_route()
    {
        $user = User::factory()->create(['nivell' => 5]);
        $clan = Clan::create([
            'nom' => 'Test Clan',
            'lider_id' => $user->id,
            'max_membres' => 20,
            'es_public' => true
        ]);
        
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson("/api/clans/{$clan->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['nom' => 'Test Clan']);
    }

    /**
     * Test 3: Comprobar ruta de Perfil de Usuario (Público).
     */
    public function test_user_public_profile_route()
    {
        $viewer = User::factory()->create();
        $target = User::factory()->create(['monstre_tipus' => 'VV', 'nivell' => 5]);
        
        $token = JWTAuth::fromUser($viewer);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson("/api/users/{$target->id}/profile");

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'nom' => $target->nom,
            'monstre_tipus' => 'VV'
        ]);
    }

    /**
     * Test Redis: Comprobar conexión y persistencia básica.
     */
    public function test_redis_connection()
    {
        try {
            Redis::set('test_key', 'loopy_ok');
            $value = Redis::get('test_key');
            
            $this->assertEquals('loopy_ok', $value);
            Redis::del('test_key');
        } catch (\Exception $e) {
            $this->fail("Redis connection failed: " . $e->getMessage());
        }
    }
}
