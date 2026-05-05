<?php

namespace App\Console\Commands;

//================================ NAMESPACES / IMPORTS ============

use App\Services\SnapshotService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Comanda per generar snapshots diaris.
 * Pot executar-se automàticament a les 23:59 o manualment amb --date.
 */
class SnapshotRunCommand extends Command
{
    protected $signature = 'snapshot:run {--date= : Data específica en format YYYY-MM-DD}';

    protected $description = 'Genera snapshots diaris per a tots els usuaris no prohibits';

    //================================ MÈTODES / FUNCIONS ===========

    public function handle(SnapshotService $snapshotService): int
    {
        $dataOpcio = $this->option('date');

        if ($dataOpcio !== null) {
            $data = $dataOpcio;
        } else {
            $data = Carbon::today()->format('Y-m-d');
        }

        $avui = Carbon::today()->format('Y-m-d');
        if ($data > $avui) {
            $this->error('No es pot generar un snapshot per a una data futura: ' . $data);
            return self::FAILURE;
        }

        $this->info('Generant snapshots per a la data: ' . $data);

        $comptador = $snapshotService->captureForAllUsers($data);

        $this->info('Snapshots generats: ' . $comptador);
        Log::info('SnapshotRunCommand: snapshots generats', [
            'data' => $data,
            'total' => $comptador,
        ]);

        return self::SUCCESS;
    }
}
