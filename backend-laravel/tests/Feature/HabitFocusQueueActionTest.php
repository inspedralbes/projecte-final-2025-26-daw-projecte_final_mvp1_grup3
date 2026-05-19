<?php

namespace Tests\Feature;

use App\Models\Habit;
use App\Models\RegistreActivitat;
use App\Models\User;
use App\Services\HabitService;
use App\Services\LogroService;
use App\Services\MissionService;
use App\Services\RedisFeedbackService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class HabitFocusQueueActionTest extends TestCase
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

    public function test_focus_update_persists_focus_session_and_mode(): void
    {
        $feedback = Mockery::mock(RedisFeedbackService::class);
        $logro = Mockery::mock(LogroService::class);
        $mission = Mockery::mock(MissionService::class);
        $mission->shouldReceive('comprovarMissioCompletada')->andReturn(null);
        $logro->shouldReceive('comprovarLogros')->zeroOrMoreTimes();
        $feedback->shouldReceive('publicarPayload')->once();

        $this->app->instance(RedisFeedbackService::class, $feedback);
        $this->app->instance(LogroService::class, $logro);
        $this->app->instance(MissionService::class, $mission);
        $service = $this->app->make(HabitService::class);
        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'usuari_id' => $user->id,
            'objectiu_vegades' => 40,
            'unitat' => 'minuts',
            'dificultat' => 'facil',
        ]);

        $service->processarAccioHabit([
            'action' => 'FOCUS_UPDATE',
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'focus_mode' => '25_5',
            'focus_minutes' => 15,
            'focus_event' => 'manual_exit',
        ]);

        $registre = RegistreActivitat::where('habit_id', $habit->id)
            ->where('focus_session', true)
            ->first();

        $this->assertNotNull($registre);
        $this->assertSame(15, (int) $registre->focus_minutes);
        $this->assertSame('25_5', $registre->focus_mode);
        $this->assertFalse((bool) $registre->acabado);
    }

    public function test_focus_update_completes_habit_when_focus_minutes_reach_goal(): void
    {
        $feedback = Mockery::mock(RedisFeedbackService::class);
        $logro = Mockery::mock(LogroService::class);
        $mission = Mockery::mock(MissionService::class);
        $mission->shouldReceive('comprovarMissioCompletada')->andReturn(null);
        $logro->shouldReceive('comprovarLogros')->once();
        $feedback->shouldReceive('publicarPayload')->once();

        $this->app->instance(RedisFeedbackService::class, $feedback);
        $this->app->instance(LogroService::class, $logro);
        $this->app->instance(MissionService::class, $mission);
        $service = $this->app->make(HabitService::class);
        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'usuari_id' => $user->id,
            'objectiu_vegades' => 20,
            'unitat' => 'minuts',
            'dificultat' => 'media',
        ]);

        $service->processarAccioHabit([
            'action' => 'FOCUS_UPDATE',
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'focus_mode' => '50_10',
            'focus_minutes' => 25,
            'focus_event' => 'work_finished',
        ]);

        $completat = RegistreActivitat::where('habit_id', $habit->id)
            ->where('acabado', true)
            ->exists();

        $this->assertTrue($completat);
    }
}
