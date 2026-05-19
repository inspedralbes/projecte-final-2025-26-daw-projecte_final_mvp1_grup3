<?php

declare(strict_types=1);

namespace App\Domains\Social\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Social\Support\SocialEngagementQuery;
use App\Domains\Social\Support\SocialPostAttachmentsResolver;
use App\Models\SocialPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Llista paginada de posts socials amb adjunts resolts.
 */
class ListSocialPostsQuery
{
    private SocialEngagementQuery $engagementQuery;

    private SocialPostAttachmentsResolver $attachmentsResolver;

    public function __construct(
        SocialEngagementQuery $engagementQuery,
        SocialPostAttachmentsResolver $attachmentsResolver
    ) {
        $this->engagementQuery = $engagementQuery;
        $this->attachmentsResolver = $attachmentsResolver;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return LengthAwarePaginator<int, SocialPost>
     */
    public function executar(int $userId): LengthAwarePaginator
    {
        $query = SocialPost::query();
        $this->engagementQuery->aplicarAPost($query, $userId);

        $posts = $query->orderBy('created_at', 'desc')->paginate(15);

        foreach ($posts as $post) {
            $post->attachments = $this->attachmentsResolver->resoldre($post);
        }

        return $posts;
    }
}
