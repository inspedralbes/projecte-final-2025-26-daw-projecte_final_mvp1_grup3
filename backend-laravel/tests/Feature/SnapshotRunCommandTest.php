<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SnapshotRunCommandTest extends TestCase
{
    use DatabaseTransactions;

    private function prepararUsuariSenseHabits(): int
    {
        return (int) DB::table('usuaris')->insertGetId([
            'nom' => 'Usuari Sense Habits',
            'email' => 'no-habits-' . uniqid() . '@example.com',
        ]);
    }

    public function test_snapshot_run_rejects_future_date(): void
    {
        $exit = Artisan::call('snapshot:run', ['--date' => '2999-01-01']);
        $this->assertNotSame(0, $exit);
    }

    public function test_snapshot_run_creates_snapshot_even_if_user_has_no_active_habits(): void
    {
        $data = '2026-05-04';
        $usuariId = $this->prepararUsuariSenseHabits();

        Artisan::call('snapshot:run', ['--date' => $data]);

        $existeix = DB::table('daily_snapshots')
            ->where('usuari_id', $usuariId)
            ->where('data', $data)
            ->exists();

        $this->assertTrue($existeix);
    }

    public function test_snapshot_run_is_idempotent_for_same_user_and_date(): void
    {
        $data = '2026-05-04';
        $usuariId = $this->prepararUsuariSenseHabits();

        Artisan::call('snapshot:run', ['--date' => $data]);
        Artisan::call('snapshot:run', ['--date' => $data]);

        $total = (int) DB::table('daily_snapshots')
            ->where('usuari_id', $usuariId)
            ->where('data', $data)
            ->count();

        $this->assertSame(1, $total);
    }
}

