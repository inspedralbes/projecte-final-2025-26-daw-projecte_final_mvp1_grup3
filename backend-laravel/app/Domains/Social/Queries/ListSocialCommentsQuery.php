<?php

declare(strict_types=1);

namespace App\Domains\Social\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Social\Support\SocialEngagementQuery;
use App\Models\SocialComment;
use Illuminate\Database\Eloquent\Collection;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Llista comentaris d'un post social.
 */
class ListSocialCommentsQuery
{
    private SocialEngagementQuery $engagementQuery;

    public function __construct(SocialEngagementQuery $engagementQuery)
    {
        $this->engagementQuery = $engagementQuery;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return Collection<int, SocialComment>
     */
    public function executar(int $userId, int $postId): Collection
    {
        $query = SocialComment::query();
        $this->engagementQuery->aplicarAComentari($query, $userId);

        return $query
            ->where('post_id', $postId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
