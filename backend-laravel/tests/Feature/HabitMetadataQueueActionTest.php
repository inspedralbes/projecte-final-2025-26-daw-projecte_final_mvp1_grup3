<?php

namespace Tests\Feature;

use App\Models\Habit;
use App\Models\User;
use App\Services\HabitService;
use App\Services\LogroService;
use App\Services\MissionService;
use App\Services\RedisFeedbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class HabitMetadataQueueActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\InsertSqlSeeder::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_and_update_actions_persist_metadata_variants(): void
    {
        $feedback = Mockery::mock(RedisFeedbackService::class);
        $logro = Mockery::mock(LogroService::class);
        $mission = Mockery::mock(MissionService::class);

        $feedback->shouldReceive('publicarPayload')->times(3);

        $this->app->instance(RedisFeedbackService::class, $feedback);
        $this->app->instance(LogroService::class, $logro);
        $this->app->instance(MissionService::class, $mission);
        $service = $this->app->make(HabitService::class);
        $user = User::factory()->create();

        $service->processarAccioHabit([
            'action' => 'CREATE',
            'user_id' => $user->id,
            'habit_data' => [
                'titol' => 'Llegir cada dia',
                'categoria_id' => 4,
                'dificultat' => 'facil',
                'objectiu_vegades' => 1,
                'unitat' => 'pàgines',
                'metadata' => [
                    'api_id' => 'book-1',
                    'titol' => 'Atomic Habits',
                    'url_imatge' => 'https://img.test/book.jpg',
                    'tipus_api' => 'google_books',
                    'api_key' => 'forbidden',
                ],
            ],
        ]);

        $habit = Habit::where('usuari_id', $user->id)->where('titol', 'Llegir cada dia')->first();

        $this->assertNotNull($habit);
        $this->assertEquals('book-1', $habit->metadata['api_id']);
        $this->assertEquals('Atomic Habits', $habit->metadata['titol']);
        $this->assertArrayNotHasKey('api_key', $habit->metadata);

        $service->processarAccioHabit([
            'action' => 'UPDATE',
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'habit_data' => [
                'metadata' => [
                    'api_id' => '',
                    'titol' => 'Manual fallback',
                    'url_imatge' => 'https://img.test/manual.jpg',
                    'tipus_api' => 'manual',
                ],
            ],
        ]);

        $habit = $habit->fresh();
        $this->assertEquals('manual', $habit->metadata['tipus_api']);
        $this->assertEquals('Manual fallback', $habit->metadata['titol']);

        $service->processarAccioHabit([
            'action' => 'UPDATE',
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'habit_data' => [
                'metadata' => null,
            ],
        ]);

        $habit = $habit->fresh();
        $this->assertNull($habit->metadata);
    }

    public function test_category_change_paths_preserve_or_clear_metadata_as_expected(): void
    {
        $feedback = Mockery::mock(RedisFeedbackService::class);
        $logro = Mockery::mock(LogroService::class);
        $mission = Mockery::mock(MissionService::class);

        $feedback->shouldReceive('publicarPayload')->times(3);

        $this->app->instance(RedisFeedbackService::class, $feedback);
        $this->app->instance(LogroService::class, $logro);
        $this->app->instance(MissionService::class, $mission);
        $service = $this->app->make(HabitService::class);
        $user = User::factory()->create();

        $service->processarAccioHabit([
            'action' => 'CREATE',
            'user_id' => $user->id,
            'habit_data' => [
                'titol' => 'Habit categoria',
                'categoria_id' => 4,
                'dificultat' => 'facil',
                'objectiu_vegades' => 1,
                'metadata' => [
                    'api_id' => 'book-22',
                    'titol' => 'Book title',
                    'url_imatge' => 'https://img.test/book22.jpg',
                    'tipus_api' => 'google_books',
                ],
            ],
        ]);

        $habit = Habit::where('usuari_id', $user->id)->where('titol', 'Habit categoria')->first();
        $this->assertNotNull($habit);

        // Camí "cancel·lar": no s'envia metadata i no s'ha de perdre.
        $service->processarAccioHabit([
            'action' => 'UPDATE',
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'habit_data' => [
                'categoria_id' => 5,
            ],
        ]);

        $habit = $habit->fresh();
        $this->assertEquals('Book title', $habit->metadata['titol']);

        // Camí "confirmar": s'envia metadata null i s'ha d'esborrar.
        $service->processarAccioHabit([
            'action' => 'UPDATE',
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'habit_data' => [
                'categoria_id' => 7,
                'metadata' => null,
            ],
        ]);

        $habit = $habit->fresh();
        $this->assertNull($habit->metadata);
        $this->assertEquals(7, (int) $habit->categoria_id);
    }
}
