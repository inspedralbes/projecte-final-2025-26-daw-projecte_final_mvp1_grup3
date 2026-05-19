<?php


/**
 * Capa Laravel: DatabaseSeeder.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace Database\Seeders;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Seeder;

//================================ MÈTODES / FUNCIONS ===========

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(InsertSqlSeeder::class);
    }
}
