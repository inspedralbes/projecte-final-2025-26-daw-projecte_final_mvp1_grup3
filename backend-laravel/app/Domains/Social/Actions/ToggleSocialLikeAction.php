<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Shared\Services\RedisFeedbackService;
use App\Domains\Social\Support\SocialLikeableTypeResolver;
use App\Models\SocialLike;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Alterna like/unlike en post o comentari i publica feedback Redis.
 */
class ToggleSocialLikeAction
{
    private SocialLikeableTypeResolver $typeResolver;

    private RedisFeedbackService $redisFeedback;

    public function __construct(
        SocialLikeableTypeResolver $typeResolver,
        RedisFeedbackService $redisFeedback
    ) {
        $this->typeResolver = $typeResolver;
        $this->redisFeedback = $redisFeedback;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array{liked: bool, likes_count: int}
     */
    public function executar(int $userId, int $likeableId, string $likeableType): array
    {
        $likeableModel = $this->typeResolver->resoldreClasse($likeableType);

        $existing = SocialLike::where('user_id', $userId)
            ->where('likeable_id', $likeableId)
            ->where('likeable_type', $likeableModel)
            ->first();

        $liked = false;
        if ($existing !== null) {
            $existing->delete();
        } else {
            SocialLike::create([
                'user_id' => $userId,
                'likeable_id' => $likeableId,
                'likeable_type' => $likeableModel,
            ]);
            $liked = true;
        }

        $count = SocialLike::where('likeable_id', $likeableId)
            ->where('likeable_type', $likeableModel)
            ->count();

        $this->redisFeedback->publicarPayload([
            'social_event' => 'like_update',
            'likeable_id' => $likeableId,
            'likeable_type' => $likeableType,
            'likes_count' => $count,
        ]);

        return [
            'liked' => $liked,
            'likes_count' => $count,
        ];
    }
}
