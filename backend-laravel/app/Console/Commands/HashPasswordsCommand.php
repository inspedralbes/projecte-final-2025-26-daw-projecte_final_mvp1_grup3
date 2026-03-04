<?php

namespace App\Console\Commands;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Administrador;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Comandament HashPasswordsCommand.
 * Recorre ADMINISTRADORS i USUARIS amb contrasenya en text pla
 * i els actualitza amb Hash::make().
 * Executar via Docker: docker compose exec backend-laravel php artisan hash:passwords
 */
class HashPasswordsCommand extends Command
{
    protected $signature = 'hash:passwords';

    protected $description = 'Converteix contrasenyes en text pla a hash bcrypt per ADMINISTRADORS i USUARIS';

    //================================ MÈTODES / FUNCIONS ===========

    public function handle(): int
    {
        $total = 0;

        $administradors = Administrador::all();
        foreach ($administradors as $admin) {
            if ($this->esContrasenyaEnTextPla($admin->contrasenya_hash)) {
                $admin->contrasenya_hash = Hash::make($admin->contrasenya_hash);
                $admin->save();
                $total++;
                $this->line('Administrador ID ' . $admin->id . ' actualitzat.');
            }
        }

        $usuaris = User::all();
        foreach ($usuaris as $usuari) {
            if ($this->esContrasenyaEnTextPla($usuari->contrasenya_hash)) {
                $usuari->contrasenya_hash = Hash::make($usuari->contrasenya_hash);
                $usuari->save();
                $total++;
                $this->line('Usuari ID ' . $usuari->id . ' actualitzat.');
            }
        }

        $this->info('Total contrasenyes hashejades: ' . $total);

        return self::SUCCESS;
    }

    /**
     * Comprova si la contrasenya sembla en text pla (bcrypt hash té 60 caràcters).
     */
    private function esContrasenyaEnTextPla(?string $hash): bool
    {
        if ($hash === null || $hash === '') {
            return false;
        }

        if (strlen($hash) < 60) {
            return true;
        }

        return false;
    }
}
