<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\HabitService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Command per resetar ratxes diàries per inactivitat.
 */
class ResetRatxesDiariesCommand extends Command
{
    /**
     * Signatura del comandament.
     *
     * @var string
     */
    protected $signature = 'ratxes:reset-diary';

    /**
     * Descripció del comandament.
     *
     * @var string
     */
    protected $description = 'Reseteja ratxes diàries per inactivitat i emet feedback';

    /**
     * Servei d\'hàbits.
     */
    protected HabitService $habitService;

    /**
     * Constructor.
     */
    public function __construct(HabitService $habitService)
    {
        parent::__construct();
        $this->habitService = $habitService;
    }

    /**
     * Executa el comandament.
     */
    public function handle(): int
    {
        $avui = Carbon::now('Europe/Madrid');
        $resetejades = $this->habitService->processarResetRatxesDiaries($avui);

        $this->info('Ratxes resetejades: ' . $resetejades);

        return self::SUCCESS;
    }
}
