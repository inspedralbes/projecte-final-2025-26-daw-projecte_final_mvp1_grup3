<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\HabitService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Command per aplicar XP proporcional diari als hàbits incomplets.
 */
class PartialXpDailyCommand extends Command
{
    /**
     * Signatura del comandament.
     *
     * @var string
     */
    protected $signature = 'habits:partial-xp';

    /**
     * Descripció del comandament.
     *
     * @var string
     */
    protected $description = 'Aplica XP proporcional als hàbits incomplets del dia anterior';

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
        $processats = $this->habitService->processarXpProporcionalDiari($avui);
        $this->info('Hàbits amb XP proporcional aplicat: ' . $processats);

        return self::SUCCESS;
    }
}
