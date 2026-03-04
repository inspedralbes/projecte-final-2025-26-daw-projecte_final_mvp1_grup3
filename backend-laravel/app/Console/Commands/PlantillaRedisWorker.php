<?php

namespace App\Console\Commands;

use App\Services\PlantillaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class PlantillaRedisWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plantilla:redis-worker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Worker que processa la cua plantilles_queue de Redis (bucle infinit)';

    /**
     * The Plantilla service instance.
     *
     * @var PlantillaService
     */
    protected PlantillaService $plantillaService;

    /**
     * The Redis queue name to listen to.
     */
    private const COLA_PLANTILLES = 'plantilles_queue';

    /**
     * Timeout for brpop (seconds).
     */
    private const TIMEOUT_BRPOP = 30;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PlantillaService $plantillaService)
    {
        parent::__construct();
        $this->plantillaService = $plantillaService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Worker Redis de Plantilles iniciat. Escoltant la cua «'.self::COLA_PLANTILLES.'»...');

        while (true) {
            try {
                $resultat = Redis::command('brpop', [self::COLA_PLANTILLES, self::TIMEOUT_BRPOP]);
            } catch (\Throwable $e) {
                Log::warning('PlantillaRedisWorker: error Redis, es reintentarà', [
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                ]);
                sleep(2);
                continue;
            }

            if (empty($resultat) || ! is_array($resultat)) {
                continue;
            }

            if (isset($resultat[1])) {
                $missatge = $resultat[1];
            } else {
                $missatge = null;
            }

            if ($missatge === null) {
                continue;
            }

            $dades = json_decode($missatge, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($dades)) {
                Log::warning('PlantillaRedisWorker: missatge JSON invàlid rebut', ['raw' => $missatge]);
                continue;
            }

            try {
                Log::info('PlantillaRedisWorker: Processant acció de plantilla', ['dades' => $dades]);
                $this->plantillaService->processarAccioPlantilla($dades);
            } catch (\Throwable $e) {
                Log::error('PlantillaRedisWorker: error processant plantilla', [
                    'dades' => $dades,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}

