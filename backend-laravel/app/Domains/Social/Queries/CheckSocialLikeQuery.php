<?php

declare(strict_types=1);

namespace App\Domains\Social\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Social\Support\SocialLikeableTypeResolver;
use App\Models\SocialLike;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Comprova si l'usuari ha fet like i retorna el recompte.
 */
class CheckSocialLikeQuery
{
    private SocialLikeableTypeResolver $typeResolver;

    public function __construct(SocialLikeableTypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array{liked: bool, count: int}
     */
    public function executar(int $userId, int $likeableId, string $likeableType): array
    {
        $likeableModel = $this->typeResolver->resoldreClasse($likeableType);

        $liked = SocialLike::where('user_id', $userId)
            ->where('likeable_id', $likeableId)
            ->where('likeable_type', $likeableModel)
            ->exists();

        $count = SocialLike::where('likeable_id', $likeableId)
            ->where('likeable_type', $likeableModel)
            ->count();

        return [
            'liked' => $liked,
            'count' => $count,
        ];
    }
}
