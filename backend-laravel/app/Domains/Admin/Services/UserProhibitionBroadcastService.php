<?php

namespace App\Domains\Admin\Services;

//================================ NAMESPACES / IMPORTS ============

use App\Models\User;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Notifica en temps real (Redis → Node → Socket) quan un usuari és prohibit.
 */
class UserProhibitionBroadcastService
{
    public function __construct(
        private RedisFeedbackService $feedbackService,
        private UserProhibitionService $prohibitionService
    ) {
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Publica account_banned al canal feedback per a la sala user_{id}.
     */
    public function notificarBanEnTempsReal(User $usuari): void
    {
        if (empty($usuari->prohibit)) {
            return;
        }

        $ban = $this->prohibitionService->evaluarProhibicio($usuari);
        if ($ban === null) {
            return;
        }

        $this->feedbackService->publicarPayload([
            'user_id' => (int) $usuari->id,
            'event' => 'account_banned',
            'ban' => $ban,
        ]);
    }
}

