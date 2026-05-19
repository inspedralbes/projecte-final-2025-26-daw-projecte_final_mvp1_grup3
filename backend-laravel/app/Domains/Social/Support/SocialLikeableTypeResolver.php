<?php

declare(strict_types=1);

namespace App\Domains\Social\Support;

//================================ NAMESPACES / IMPORTS ============

use App\Models\SocialComment;
use App\Models\SocialPost;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Converteix tipus curt (post|comment) al nom de classe Eloquent.
 */
class SocialLikeableTypeResolver
{
    //================================ MÈTODES / FUNCIONS ===========

    public function resoldreClasse(string $likeableType): string
    {
        if ($likeableType === 'post') {
            return SocialPost::class;
        }

        return SocialComment::class;
    }
}
