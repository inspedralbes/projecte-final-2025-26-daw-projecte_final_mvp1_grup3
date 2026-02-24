<?php

namespace App\Console\Commands;

//================================ NAMESPACES / IMPORTS ============

use App\Services\AdminActionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Comandament AdminRedisWorker.
 * Escolta la cua 'admin_queue' amb brpop i processa accions CUD
 * mitjançant AdminActionService.
 */
class AdminRedisWorker extends Command
{
    protected $signature = 'admin:redis-worker';

    protected $description = 'Worker que processa la cua admin_queue de Redis (accions CUD admin)';

    private AdminActionService $adminActionService;

    private const COLA_ADMIN = 'admin_queue';

    private const TIMEOUT_BRPOP = 30;

    //================================ MÈTODES / FUNCIONS ===========

    public function __construct(AdminActionService $adminActionService)
    {
        parent::__construct();
        $this->adminActionService = $adminActionService;
    }

    /**
     * Executa el comandament: bucle infinit amb brpop i processament.
     */
    public function handle(): int
    {
        $this->info('Admin Redis Worker iniciat. Escoltant la cua «' . self::COLA_ADMIN . '»...');

        while (true) {
            try {
                $resultat = Redis::command('brpop', [self::COLA_ADMIN, self::TIMEOUT_BRPOP]);
            } catch (\Throwable $e) {
                Log::warning('AdminRedisWorker: error Redis, es reintentarà', [
                    'error' => $e->getMessage(),
                ]);
                sleep(2);
                continue;
            }

            if (empty($resultat) || !is_array($resultat)) {
                continue;
            }

            $missatge = $resultat[1] ?? null;
            if ($missatge === null) {
                continue;
            }

            $this->info('Missatge rebut de Redis: ' . $missatge);

            $dades = json_decode($missatge, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($dades)) {
                Log::warning('AdminRedisWorker: missatge JSON invàlid rebut', ['raw' => $missatge]);
                continue;
            }

            try {
                $this->adminActionService->processarAccio($dades);
            } catch (\Throwable $e) {
                Log::error('AdminRedisWorker: error processant acció', [
                    'dades' => $dades,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }
}
