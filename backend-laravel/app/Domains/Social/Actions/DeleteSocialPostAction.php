<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Models\SocialPost;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Elimina un post social si pertany a l'usuari.
 */
class DeleteSocialPostAction
{
    //================================ MÈTODES / FUNCIONS ===========

    public function executar(int $userId, int $postId): void
    {
        $post = SocialPost::where('id', $postId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $post->delete();
    }
}
