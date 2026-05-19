<?php


/**
 * Capa Laravel: RedisSnapshotPipelineTest.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace Tests\Feature;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

//================================ MÈTODES / FUNCIONS ===========

class RedisSnapshotPipelineTest extends TestCase
{
    private function prepararUsuariMinim(): int
    {
        return (int) DB::table('usuaris')->insertGetId([
            'nom' => 'Usuari Redis',
            'email' => 'redis-user-' . uniqid() . '@example.com',
        ]);
    }

    public function test_redis_unified_worker_processes_snapshot_queue_message(): void
    {
        $data = '2026-05-04';
        $usuariId = $this->prepararUsuariMinim();

        try {
            $missatge = json_encode([
                'event' => 'snapshot:run',
                'date' => $data,
            ]);
            $this->assertNotFalse($missatge);

            // Important: aquest test NO pot usar transaccions,
            // perquè el worker pot usar una connexió diferent i no veure dades no confirmades.
            Redis::command('lpush', ['snapshot_queue', $missatge]);

            Artisan::call('redis:unified-worker', [
                '--once' => true,
                '--timeout' => 2,
            ]);

            $existeix = DB::table('daily_snapshots')
                ->where('usuari_id', $usuariId)
                ->where('data', $data)
                ->exists();

            $this->assertTrue($existeix);
        } finally {
            DB::table('daily_snapshots')->where('usuari_id', $usuariId)->where('data', $data)->delete();
            DB::table('usuaris')->where('id', $usuariId)->delete();
        }
    }

    public function test_redis_snapshot_pipeline_is_idempotent_for_same_date_message(): void
    {
        $data = '2026-05-05';
        $usuariId = $this->prepararUsuariMinim();

        try {
            $missatge = json_encode([
                'event' => 'snapshot:run',
                'date' => $data,
            ]);
            $this->assertNotFalse($missatge);

            Redis::command('lpush', ['snapshot_queue', $missatge]);
            Redis::command('lpush', ['snapshot_queue', $missatge]);

            Artisan::call('redis:unified-worker', [
                '--once' => true,
                '--timeout' => 2,
            ]);
            Artisan::call('redis:unified-worker', [
                '--once' => true,
                '--timeout' => 2,
            ]);

            $total = (int) DB::table('daily_snapshots')
                ->where('usuari_id', $usuariId)
                ->where('data', $data)
                ->count();

            $this->assertSame(1, $total);
        } finally {
            DB::table('daily_snapshots')->where('usuari_id', $usuariId)->where('data', $data)->delete();
            DB::table('usuaris')->where('id', $usuariId)->delete();
        }
    }
}

