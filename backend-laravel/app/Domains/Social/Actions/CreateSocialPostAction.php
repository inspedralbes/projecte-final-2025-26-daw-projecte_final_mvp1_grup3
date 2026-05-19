<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Shared\Services\RedisFeedbackService;
use App\Domains\Social\Support\SocialEngagementQuery;
use App\Domains\Social\Support\SocialPostAttachmentsResolver;
use App\Models\SocialPost;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Crea un post social i publica esdeveniment Redis.
 */
class CreateSocialPostAction
{
    private SocialEngagementQuery $engagementQuery;

    private SocialPostAttachmentsResolver $attachmentsResolver;

    private RedisFeedbackService $redisFeedback;

    public function __construct(
        SocialEngagementQuery $engagementQuery,
        SocialPostAttachmentsResolver $attachmentsResolver,
        RedisFeedbackService $redisFeedback
    ) {
        $this->engagementQuery = $engagementQuery;
        $this->attachmentsResolver = $attachmentsResolver;
        $this->redisFeedback = $redisFeedback;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $validated
     */
    public function executar(int $userId, array $validated): SocialPost
    {
        $post = SocialPost::create([
            'user_id' => $userId,
            'content' => $validated['content'],
            'habit_id' => $validated['habit_id'] ?? null,
            'plantilla_id' => $validated['plantilla_id'] ?? null,
            'attachments' => $validated['attachments'] ?? [],
        ]);

        $query = SocialPost::where('id', $post->id);
        $this->engagementQuery->aplicarAPost($query, $userId);
        $post = $query->firstOrFail();

        $post->attachments = $this->attachmentsResolver->resoldre($post);

        $this->redisFeedback->publicarPayload([
            'social_event' => 'new_post',
            'post' => $post,
        ]);

        return $post;
    }
}
