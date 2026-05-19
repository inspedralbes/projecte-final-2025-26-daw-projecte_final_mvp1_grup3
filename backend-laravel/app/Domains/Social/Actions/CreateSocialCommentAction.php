<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Shared\Services\RedisFeedbackService;
use App\Domains\Social\Support\SocialEngagementQuery;
use App\Models\SocialComment;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Crea un comentari social amb control de profunditat.
 */
class CreateSocialCommentAction
{
    private SocialEngagementQuery $engagementQuery;

    private RedisFeedbackService $redisFeedback;

    public function __construct(
        SocialEngagementQuery $engagementQuery,
        RedisFeedbackService $redisFeedback
    ) {
        $this->engagementQuery = $engagementQuery;
        $this->redisFeedback = $redisFeedback;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $validated
     * @return array{success: bool, comment?: SocialComment, error?: string, status?: int}
     */
    public function executar(int $userId, array $validated): array
    {
        $depthLevel = 0;
        if (!empty($validated['parent_id'])) {
            $parent = SocialComment::findOrFail((int) $validated['parent_id']);
            if ($parent->depth_level >= 3) {
                return [
                    'success' => false,
                    'error' => 'No es pot respondre a un comentari de profunditat 3',
                    'status' => 422,
                ];
            }
            $depthLevel = $parent->depth_level + 1;
        }

        $comment = SocialComment::create([
            'post_id' => $validated['post_id'],
            'user_id' => $userId,
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
            'depth_level' => $depthLevel,
        ]);

        $query = SocialComment::where('id', $comment->id);
        $this->engagementQuery->aplicarAComentari($query, $userId);
        $comment = $query->firstOrFail();

        $this->redisFeedback->publicarPayload([
            'social_event' => 'new_comment',
            'comment' => $comment,
        ]);

        return [
            'success' => true,
            'comment' => $comment,
        ];
    }
}
