<?php

namespace App\Console\Commands;

//================================ NAMESPACES / IMPORTS ============

use App\Console\Commands\QueueHandlers\AdminQueueHandler;
use App\Console\Commands\QueueHandlers\HabitQueueHandler;
use App\Console\Commands\QueueHandlers\PlantillaQueueHandler;
use App\Console\Commands\QueueHandlers\RouletteQueueHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Worker únic que processa totes les cues Redis.
 * Escolta habits_queue, plantilles_queue, admin_queue, roulette_queue i snapshot_queue
 * mitjançant BRPOP multillista i delega als QueueHandlers corresponents.
 */
class UnifiedRedisWorker extends Command
{
    protected $signature = 'redis:unified-worker {--once : Processa un sol missatge i surt (per tests)} {--timeout= : Timeout BRPOP en segons}';

    protected $description = 'Worker únic que processa totes les cues Redis (habits, plantilles, admin, ruleta, snapshots)';

    /**
     * Cues a escoltar (BRPOP multillista).
     */
    private const CUES = ['habits_queue', 'plantilles_queue', 'admin_queue', 'roulette_queue', 'snapshot_queue'];

    /**
     * Timeout per BRPOP (segons).
     */
    private const TIMEOUT_BRPOP = 30;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Executa el comandament: bucle infinit amb BRPOP multillista.
     */
    public function handle(): int
    {
        $this->info('Unified Redis Worker iniciat. Escoltant: ' . implode(', ', self::CUES));

        $processarUnCop = (bool) $this->option('once');

        $timeoutOpcio = $this->option('timeout');
        if ($timeoutOpcio !== null) {
            $timeout = (int) $timeoutOpcio;
        } else {
            $timeout = self::TIMEOUT_BRPOP;
        }
        if ($timeout < 0) {
            $timeout = self::TIMEOUT_BRPOP;
        }

        while (true) {
            try {
                $args = array_merge(self::CUES, [$timeout]);
                $resultat = Redis::command('brpop', $args);
            } catch (\Throwable $e) {
                Log::warning('UnifiedRedisWorker: error Redis, es reintentarà', [
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                ]);
                sleep(2);
                continue;
            }

            if (empty($resultat) || ! is_array($resultat)) {
                if ($processarUnCop) {
                    return self::SUCCESS;
                }
                continue;
            }

            $nomCua = isset($resultat[0]) ? $resultat[0] : null;
            $missatge = isset($resultat[1]) ? $resultat[1] : null;

            if ($nomCua === null || $missatge === null) {
                continue;
            }

            $dades = json_decode($missatge, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($dades)) {
                Log::warning('UnifiedRedisWorker: missatge JSON invàlid rebut', [
                    'cua' => $nomCua,
                    'raw' => $missatge,
                ]);
                continue;
            }

            try {
                $this->despatxarSegonsCua($nomCua, $dades);
            } catch (\Throwable $e) {
                Log::error('UnifiedRedisWorker: error processant', [
                    'cua' => $nomCua,
                    'dades' => $dades,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($processarUnCop) {
                return self::SUCCESS;
            }
        }

        return self::SUCCESS;
    }

    /**
     * Despatxa el missatge al handler corresponent segons la cua.
     *
     * @param  string  $nomCua
     * @param  array<string, mixed>  $dades
     */
    private function despatxarSegonsCua(string $nomCua, array $dades): void
    {
        if ($nomCua === 'snapshot_queue') {
            $params = [];
            if (isset($dades['date'])) {
                $params['--date'] = $dades['date'];
            }
            Artisan::call('snapshot:run', $params);
            Log::info('UnifiedRedisWorker: snapshot:run executat', ['dades' => $dades]);
            return;
        }

        $handler = match ($nomCua) {
            'habits_queue' => app(HabitQueueHandler::class),
            'plantilles_queue' => app(PlantillaQueueHandler::class),
            'admin_queue' => app(AdminQueueHandler::class),
            'roulette_queue' => app(RouletteQueueHandler::class),
            default => null,
        };

        if ($handler !== null) {
            $handler->handle($dades);
        } else {
            Log::warning('UnifiedRedisWorker: cua desconeguda', ['cua' => $nomCua]);
        }
    }
}
