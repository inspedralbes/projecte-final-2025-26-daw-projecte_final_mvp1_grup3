<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\User;
use Illuminate\Support\Facades\Hash;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Actualització de dades de compte (nom, email, contrasenya opcional).
 */
class UserAccountUpdateService
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  string|null  $novaContrasenya  Text pla; si null o buit, no es canvia la contrasenya.
     * @return array{ok: bool, errors?: array<string, string>, user?: User}
     */
    public function actualitzarPerUsuariId(int $usuariId, string $nom, string $email, ?string $novaContrasenya): array
    {
        $nomTrim = trim($nom);
        $emailTrim = trim($email);

        if ($nomTrim === '') {
            return ['ok' => false, 'errors' => ['nom' => 'required']];
        }
        if ($emailTrim === '') {
            return ['ok' => false, 'errors' => ['email' => 'required']];
        }
        if (!filter_var($emailTrim, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'errors' => ['email' => 'invalid']];
        }

        $usuari = User::find($usuariId);
        if (!$usuari) {
            return ['ok' => false, 'errors' => ['general' => 'not_found']];
        }

        $altre = User::where('email', $emailTrim)->where('id', '!=', $usuariId)->first();
        if ($altre) {
            return ['ok' => false, 'errors' => ['email' => 'taken']];
        }

        $usuari->nom = $nomTrim;
        $usuari->email = $emailTrim;

        if ($novaContrasenya !== null) {
            $pwdTrim = trim($novaContrasenya);
            if ($pwdTrim !== '') {
                if (strlen($pwdTrim) < 6) {
                    return ['ok' => false, 'errors' => ['password' => 'min_6']];
                }
                $usuari->contrasenya_hash = Hash::make($pwdTrim);
            }
        }

        $usuari->save();

        return ['ok' => true, 'user' => $usuari];
    }
}
