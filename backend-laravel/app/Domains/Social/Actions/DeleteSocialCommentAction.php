<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\SocialComment;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Elimina un comentari social si pertany a l'usuari.
 */
class DeleteSocialCommentAction
{
    //================================ MÈTODES / FUNCIONS ===========

    public function executar(int $userId, int $commentId): void
    {
        $comment = SocialComment::where('id', $commentId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $comment->delete();
    }
}
