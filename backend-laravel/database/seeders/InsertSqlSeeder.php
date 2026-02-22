<?php

namespace Database\Seeders;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

//================================ MÈTODES / FUNCIONS ===========

/**
 * Executa el fitxer database/insert.sql per carregar les dades inicials.
 * Utilitza /var/database (Docker) o ../database des del projecte.
 */
class InsertSqlSeeder extends Seeder
{
    public function run(): void
    {
        $rutaDocker = '/var/database/insert.sql';
        $rutaLocal = dirname(base_path()) . '/database/insert.sql';

        if (file_exists($rutaDocker)) {
            $ruta = $rutaDocker;
        } else {
            $ruta = $rutaLocal;
        }

        if (! file_exists($ruta)) {
            $this->command->error("No s'ha trobat insert.sql a: {$ruta}");
            return;
        }

        $sql = file_get_contents($ruta);
        $sql = preg_replace('/^INSERTS:\s*\n/', '', $sql);

        // Executar cada sentència per separat (PostgreSQL no permet múltiples en un sol exec)
        $sentencies = preg_split('/;\s*\n\s*(?=INSERT INTO)/i', $sql);

        foreach ($sentencies as $sentencia) {
            $sentencia = trim($sentencia);
            // Eliminar línies de comentari al principi
            $sentencia = preg_replace('/^(\s*--[^\n]*\n)+/m', '', $sentencia);
            $sentencia = trim($sentencia);

            if ($sentencia !== '' && preg_match('/^INSERT\s+INTO/i', $sentencia)) {
                if (substr(rtrim($sentencia), -1) !== ';') {
                    $sentencia .= ';';
                }
                DB::unprepared($sentencia);
            }
        }

        $this->command->info('Dades de insert.sql carregades correctament.');
    }
}
