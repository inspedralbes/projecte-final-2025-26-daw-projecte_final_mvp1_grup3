<?php

namespace App\Console\Commands;

//================================ NAMESPACES / IMPORTS ============

use App\Services\RouletteService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Worker Redis per a la ruleta diària.
 * Escolta la cua 'roulette_queue' i processa tirades.
 */
class RouletteRedisWorker extends Command
{
    /**
     * Signatura del comandament.
     *
     * @var string
     */
    protected $signature = 'roulette:redis-worker';

    /**
     * Descripció del comandament.
     *
     * @var string
     */
    protected $description = 'Worker que processa la cua roulette_queue de Redis (tirades ruleta)';

    /**
     * Servei de ruleta.
     */
    protected RouletteService $rouletteService;

    /**
     * Nom de la cua Redis a escoltar.
     */
    private const COLA_RULETA = 'roulette_queue';

    /**
     * Timeout per brpop (segons).
     */
    private const TIMEOUT_BRPOP = 30;

    public function __construct(RouletteService $rouletteService)
    {
        parent::__construct();
        $this->rouletteService = $rouletteService;
    }

    /**
     * Executa el comandament: bucle infinit amb brpop i processament.
     */
    public function handle(): int
    {
        $this->info('Worker Redis de Ruleta iniciat. Escoltant la cua «' . self::COLA_RULETA . '»...');

        while (true) {
            try {
                // A. Esperar missatge de la cua amb timeout
                $resultat = Redis::command('brpop', [self::COLA_RULETA, self::TIMEOUT_BRPOP]);
            } catch (\Throwable $e) {
                // B. Log d'error de Redis i reintentar
                Log::warning('RouletteRedisWorker: error Redis, es reintentarà', [
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                ]);
                sleep(2);
                continue;
            }

            // C. Validar resultat del BRPOP
            if (empty($resultat) || ! is_array($resultat)) {
                continue;
            }

            $missatge = $resultat[1] ?? null;
            if ($missatge === null) {
                continue;
            }

            // D. Parsejar JSON
            $dades = json_decode($missatge, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($dades)) {
                Log::warning('RouletteRedisWorker: missatge JSON invàlid rebut', ['raw' => $missatge]);
                continue;
            }

            try {
                // E. Processar la tirada
                $this->rouletteService->processarTirada($dades);
            } catch (\Throwable $e) {
                Log::error('RouletteRedisWorker: error processant ruleta', [
                    'dades' => $dades,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}
