<?php

namespace App\Console\Commands;

//================================ NAMESPACES / IMPORTS ============

use App\Services\AdminActionService;
use App\Services\HabitService;
use App\Services\PlantillaService;
use App\Services\RouletteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Worker únic que processa totes les cues Redis.
 * Escolta habits_queue, plantilles_queue, admin_queue i roulette_queue
 * mitjançant BRPOP multillista i despatxa al servei corresponent.
 */
class UnifiedRedisWorker extends Command
{
    protected $signature = 'redis:unified-worker';

    protected $description = 'Worker únic que processa totes les cues Redis (habits, plantilles, admin, ruleta)';

    /**
     * Cues a escoltar (BRPOP multillista).
     */
    private const CUES = ['habits_queue', 'plantilles_queue', 'admin_queue', 'roulette_queue'];

    /**
     * Timeout per BRPOP (segons).
     */
    private const TIMEOUT_BRPOP = 30;

    /**
     * @var HabitService
     */
    protected HabitService $habitService;

    /**
     * @var PlantillaService
     */
    protected PlantillaService $plantillaService;

    /**
     * @var AdminActionService
     */
    protected AdminActionService $adminActionService;

    /**
     * @var RouletteService
     */
    protected RouletteService $rouletteService;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(
        HabitService $habitService,
        PlantillaService $plantillaService,
        AdminActionService $adminActionService,
        RouletteService $rouletteService
    ) {
        parent::__construct();
        $this->habitService = $habitService;
        $this->plantillaService = $plantillaService;
        $this->adminActionService = $adminActionService;
        $this->rouletteService = $rouletteService;
    }

    /**
     * Executa el comandament: bucle infinit amb BRPOP multillista.
     */
    public function handle(): int
    {
        $this->info('Unified Redis Worker iniciat. Escoltant: ' . implode(', ', self::CUES));

        while (true) {
            try {
                $args = array_merge(self::CUES, [self::TIMEOUT_BRPOP]);
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
        }

        return self::SUCCESS;
    }

    /**
     * Despatxa el missatge al servei corresponent segons la cua.
     *
     * @param  string  $nomCua
     * @param  array<string, mixed>  $dades
     */
    private function despatxarSegonsCua(string $nomCua, array $dades): void
    {
        if ($nomCua === 'habits_queue') {
            $this->habitService->processarAccioHabit($dades);
        } elseif ($nomCua === 'plantilles_queue') {
            $this->plantillaService->processarAccioPlantilla($dades);
        } elseif ($nomCua === 'admin_queue') {
            $this->adminActionService->processarAccio($dades);
        } elseif ($nomCua === 'roulette_queue') {
            $this->rouletteService->processarTirada($dades);
        } else {
            Log::warning('UnifiedRedisWorker: cua desconeguda', ['cua' => $nomCua]);
        }
    }
}
