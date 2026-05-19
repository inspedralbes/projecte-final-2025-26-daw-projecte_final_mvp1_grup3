<?php


/**
 * Capa Laravel: WelcomeEmailService.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace App\Domains\User\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Mail\WelcomeUserMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envia el correu de benvinguda només el primer cop que l'usuari inicia sessió.
 */
//================================ MÈTODES / FUNCIONS ===========

class WelcomeEmailService
{
    public function enviarSiPrimeraConnexio(User $usuari): void
    {
        if (! (bool) config('services.welcome_email_enabled', true)) {
            return;
        }

        if ($usuari->email === null || $usuari->email === '') {
            return;
        }

        try {
            DB::transaction(function () use ($usuari) {
                $u = User::lockForUpdate()->find($usuari->id);
                if ($u === null || $u->primer_login_correu_enviat_at !== null) {
                    return;
                }

                Mail::to($u->email)->send(new WelcomeUserMail($u));

                $u->primer_login_correu_enviat_at = now();
                $u->save();
            });
        } catch (\Throwable $e) {
            Log::error('Correu benvinguda: '.$e->getMessage(), [
                'usuari_id' => $usuari->id,
                'exception' => $e,
            ]);
        }
    }
}

