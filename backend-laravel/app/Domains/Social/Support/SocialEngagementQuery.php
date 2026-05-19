<?php

declare(strict_types=1);

namespace App\Domains\Social\Support;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Builder;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Eager loads compartits per posts i comentaris socials.
 */
class SocialEngagementQuery
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  Builder<\App\Models\SocialPost>  $query
     */
    public function aplicarAPost(Builder $query, int $userId): void
    {
        $query->with(['user', 'habit', 'plantilla.habits'])
            ->withCount(['comments', 'likes'])
            ->withExists(['likes as liked_by_current_user' => function ($subQuery) use ($userId) {
                $subQuery->where('user_id', $userId);
            }]);
    }

    /**
     * @param  Builder<\App\Models\SocialComment>  $query
     */
    public function aplicarAComentari(Builder $query, int $userId): void
    {
        $query->with('user')
            ->withCount('likes')
            ->withExists(['likes as liked_by_current_user' => function ($subQuery) use ($userId) {
                $subQuery->where('user_id', $userId);
            }]);
    }
}
