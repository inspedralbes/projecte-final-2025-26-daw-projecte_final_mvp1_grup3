<?php

declare(strict_types=1);

namespace App\Domains\Social\Queries;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Social\Support\SocialEngagementQuery;
use App\Domains\Social\Support\SocialPostAttachmentsResolver;
use App\Models\SocialPost;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Obté un post social per id amb relacions i adjunts.
 */
class GetSocialPostQuery
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

    public function executar(int $userId, int $postId): SocialPost
    {
        $query = SocialPost::query();
        $this->engagementQuery->aplicarAPost($query, $userId);

        $post = $query->findOrFail($postId);
        $post->attachments = $this->attachmentsResolver->resoldre($post);

        return $post;
    }
}
