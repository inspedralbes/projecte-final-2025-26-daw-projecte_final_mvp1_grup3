<?php

namespace App\Console\Commands;

//================================ NAMESPACES / IMPORTS ============

use App\Services\HabitService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Comandament RedisWorker.
 * Executa un bucle infinit que escolta la cua 'habits_queue' amb brpop bloquejant
 * i processa les accions d'hàbits completats mitjançant HabitService.
 */
class RedisWorker extends Command
{
    /**
     * Signatura del comandament.
     *
     * @var string
     */
    protected $signature = 'habits:redis-worker';

    /**
     * Descripció del comandament.
     *
     * @var string
     */
    protected $description = 'Worker que processa la cua habits_queue de Redis (bucle infinit)';

    /**
     * Servei de processament d'hàbits.
     *
     * @var HabitService
     */
    protected HabitService $habitService;

    /**
     * Nom de la cua Redis a escoltar.
     */
    private const COLA_HABITS = 'habits_queue';

    /**
     * Timeout per brpop: 0 = bloqueig indefinit.
     */
    private const TIMEOUT_BRPOP = 0;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Constructor. Injecció del servei d'hàbits.
     */
    public function __construct(HabitService $habitService)
    {
        parent::__construct();
        $this->habitService = $habitService;
    }

    /**
     * Executa el comandament: bucle infinit amb brpop i processament.
     * Extreu elements de la cua bloquejant fins rebre un nou missatge.
     */
    public function handle(): int
    {
        $this->info('Worker Redis iniciat. Escoltant la cua «' . self::COLA_HABITS . '»...');

        while (true) {
            // A. brpop bloqueja fins que arribi un element o es faci timeout
            $resultat = Redis::command('brpop', [self::COLA_HABITS, self::TIMEOUT_BRPOP]);

            // B. Si no hi ha resultat (timeout o error), continuar el bucle
            if (empty($resultat) || ! is_array($resultat)) {
                continue;
            }

            // C. brpop retorna [clau, valor]; el valor és el missatge JSON
            if (isset($resultat[1])) {
                $missatge = $resultat[1];
            } else {
                $missatge = null;
            }

            if ($missatge === null) {
                continue;
            }

            // D. Parseig del missatge JSON
            $dades = json_decode($missatge, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($dades)) {
                Log::warning('RedisWorker: missatge JSON invàlid rebut', ['raw' => $missatge]);
                continue;
            }

            // E. Processament mitjançant el servei d'hàbits
            try {
                $this->habitService->processarHabitCompletat($dades);
            } catch (\Throwable $e) {
                Log::error('RedisWorker: error processant hàbit', [
                    'dades' => $dades,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}
