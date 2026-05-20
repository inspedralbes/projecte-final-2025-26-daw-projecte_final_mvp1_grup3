<?php

declare(strict_types=1);

namespace Tests\Feature\Habits;

use App\Domains\Gamification\Services\GamificationService;
use App\Domains\Habits\Actions\CompleteHabitAction;
use App\Models\Habit;
use App\Models\Ratxa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreakOnHabitCompleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\InsertSqlSeeder::class);
    }

    public function test_game_state_load_does_not_increment_streak(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-20 10:00:00', 'Europe/Madrid'));

        $user = User::factory()->create();
        Ratxa::create([
            'usuari_id' => $user->id,
            'ratxa_actual' => 3,
            'ratxa_maxima' => 5,
            'ultima_data' => '2026-05-19',
        ]);

        $service = $this->app->make(GamificationService::class);
        $estat = $service->obtenirEstatGamificacio($user->id);

        $this->assertSame(3, $estat['ratxa_actual']);
        $this->assertFalse($estat['streak_incremented']);

        $ratxa = Ratxa::where('usuari_id', $user->id)->first();
        $this->assertSame(3, (int) $ratxa->ratxa_actual);
        $this->assertSame('2026-05-19', $ratxa->ultima_data);

        Carbon::setTestNow();
    }

    public function test_completing_habit_increments_streak_on_consecutive_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-20 12:00:00', 'Europe/Madrid'));

        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'usuari_id' => $user->id,
            'objectiu_vegades' => 1,
            'dificultat' => 'facil',
        ]);
        Ratxa::create([
            'usuari_id' => $user->id,
            'ratxa_actual' => 2,
            'ratxa_maxima' => 2,
            'ultima_data' => '2026-05-19',
        ]);

        $action = $this->app->make(CompleteHabitAction::class);
        $resultat = $action->executar([
            'habit_id' => $habit->id,
            'user_id' => $user->id,
        ]);

        $this->assertTrue($resultat['success']);
        $this->assertTrue($resultat['xp_update']['streak_incremented']);
        $this->assertSame(3, $resultat['xp_update']['ratxa_actual']);

        $ratxa = Ratxa::where('usuari_id', $user->id)->first();
        $this->assertSame(3, (int) $ratxa->ratxa_actual);
        $this->assertSame('2026-05-20', $ratxa->ultima_data);

        Carbon::setTestNow();
    }

    public function test_completing_habit_increments_streak_when_zero_with_ultima_data_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-20 12:00:00', 'Europe/Madrid'));

        $user = User::factory()->create();
        $habit = Habit::factory()->create([
            'usuari_id' => $user->id,
            'objectiu_vegades' => 1,
            'dificultat' => 'facil',
        ]);
        Ratxa::create([
            'usuari_id' => $user->id,
            'ratxa_actual' => 0,
            'ratxa_maxima' => 0,
            'ultima_data' => '2026-05-20',
        ]);

        $action = $this->app->make(CompleteHabitAction::class);
        $resultat = $action->executar(['habit_id' => $habit->id, 'user_id' => $user->id]);

        $this->assertTrue($resultat['success']);
        $this->assertTrue($resultat['xp_update']['streak_incremented']);
        $this->assertSame(1, $resultat['xp_update']['ratxa_actual']);

        Carbon::setTestNow();
    }

    public function test_second_habit_same_day_does_not_increment_streak_again(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-20 18:00:00', 'Europe/Madrid'));

        $user = User::factory()->create();
        $habit1 = Habit::factory()->create([
            'usuari_id' => $user->id,
            'objectiu_vegades' => 1,
            'dificultat' => 'facil',
        ]);
        $habit2 = Habit::factory()->create([
            'usuari_id' => $user->id,
            'objectiu_vegades' => 1,
            'dificultat' => 'facil',
        ]);

        $action = $this->app->make(CompleteHabitAction::class);

        $primer = $action->executar(['habit_id' => $habit1->id, 'user_id' => $user->id]);
        $this->assertTrue($primer['xp_update']['streak_incremented']);
        $this->assertSame(1, $primer['xp_update']['ratxa_actual']);

        $segon = $action->executar(['habit_id' => $habit2->id, 'user_id' => $user->id]);
        $this->assertTrue($segon['success']);
        $this->assertFalse($segon['xp_update']['streak_incremented']);
        $this->assertSame(1, $segon['xp_update']['ratxa_actual']);

        Carbon::setTestNow();
    }
}
