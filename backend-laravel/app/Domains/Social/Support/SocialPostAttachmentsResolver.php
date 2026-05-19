<?php

declare(strict_types=1);

namespace App\Domains\Social\Support;

//================================ NAMESPACES / IMPORTS ============

use App\Models\Habit;
use App\Models\Plantilla;
use App\Models\SocialPost;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Resol adjunts d'un post social amb models relacionats.
 */
class SocialPostAttachmentsResolver
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @return array<int, array<string, mixed>>
     */
    public function resoldre(SocialPost $post): array
    {
        $attachments = $post->attachments;
        if (is_string($attachments)) {
            $decoded = json_decode($attachments, true);
            if (is_array($decoded)) {
                $attachments = $decoded;
            }
        }
        if (!is_array($attachments)) {
            $attachments = [];
        }

        if (count($attachments) === 0) {
            if ($post->habit_id !== null) {
                $attachments[] = [
                    'type' => 'habit',
                    'id' => $post->habit_id,
                ];
            }
            if ($post->plantilla_id !== null) {
                $attachments[] = [
                    'type' => 'plantilla',
                    'id' => $post->plantilla_id,
                ];
            }
        }

        $result = [];
        foreach ($attachments as $att) {
            if (!isset($att['type']) || !isset($att['id'])) {
                continue;
            }

            if ($att['type'] === 'habit') {
                $habit = Habit::find($att['id']);
                if ($habit !== null) {
                    $att['habit'] = $habit;
                    $att['titol'] = $habit->titol;
                    $result[] = $att;
                }
                continue;
            }

            if ($att['type'] === 'plantilla') {
                $plantilla = Plantilla::with('habits')->find($att['id']);
                if ($plantilla !== null) {
                    $att['plantilla'] = $plantilla;
                    $att['titol'] = $plantilla->titol;
                    $result[] = $att;
                }
            }
        }

        return $result;
    }
}
