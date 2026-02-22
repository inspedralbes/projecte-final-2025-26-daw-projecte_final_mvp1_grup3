<?php

namespace App\Console\Commands;

//================================ NAMESPACES / IMPORTS ============

use App\Services\PlantillaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Comandament PlantillaRedisWorker.
 * Executa un bucle infinit que escolta la cua 'plantilles_queue' amb brpop bloquejant
 * i processa les accions de plantilles (CRUD) mitjançant PlantillaService.
 */
class PlantillaRedisWorker extends Command
{
    /**
     * Signatura del comandament.
     *
     * @var string
     */
    protected $signature = 'plantilles:redis-worker';

    /**
     * Descripció del comandament.
     *
     * @var string
     */
    protected $description = 'Worker que processa la cua plantilles_queue de Redis (bucle infinit)';

    /**
     * Servei de processament de plantilles.
     *
     * @var PlantillaService
     */
    protected PlantillaService $plantillaService;

    /**
     * Nom de la cua Redis a escoltar.
     */
    private const COLA_PLANTILLES = 'plantilles_queue';

    /**
     * Timeout per brpop (segons).
     */
    private const TIMEOUT_BRPOP = 30;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Constructor. Injecció del servei de plantilles.
     */
    public function __construct(PlantillaService $plantillaService)
    {
        parent::__construct();
        $this->plantillaService = $plantillaService;
    }

    /**
     * Executa el comandament: bucle infinit amb brpop i processament.
     */
    public function handle(): int
    {
        $this->info('Worker Redis iniciat. Escoltant la cua «' . self::COLA_PLANTILLES . '»...');

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

            $missatge = $resultat[1] ?? null;

            if ($missatge === null) {
                continue;
            }

            $dades = json_decode($missatge, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($dades)) {
                Log::warning('PlantillaRedisWorker: missatge JSON invàlid rebut', ['raw' => $missatge]);
                continue;
            }

            try {
                $this->plantillaService->processarAccioPlantilla($dades);
            } catch (\Throwable $e) {
                Log::error('PlantillaRedisWorker: error processant plantilla', [
                    'dades' => $dades,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}
