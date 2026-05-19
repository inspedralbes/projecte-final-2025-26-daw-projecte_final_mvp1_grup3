<?php

declare(strict_types=1);

namespace App\Domains\Social\Actions;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Admin\Services\AdminReportBroadcastService;
use App\Models\Report;
use App\Models\SocialComment;
use App\Models\SocialPost;
use App\Models\User;
use App\Models\UserReport;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Crea una denúncia social (post, comentari o usuari).
 */
class CreateSocialReportAction
{
    private AdminReportBroadcastService $reportBroadcast;

    public function __construct(AdminReportBroadcastService $reportBroadcast)
    {
        $this->reportBroadcast = $reportBroadcast;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * @param  array<string, mixed>  $validated
     * @return Report|UserReport
     */
    public function executar(int $userId, array $validated): Report|UserReport
    {
        $contentType = $validated['content_type'];
        $contentId = (int) $validated['content_id'];

        if ($contentType === 'user') {
            User::findOrFail($contentId);

            $report = UserReport::create([
                'usuari_id' => $userId,
                'reportat_id' => $contentId,
                'motiu' => $validated['motiu'] ?? $validated['reason'] ?? 'Altres',
                'detalls' => $validated['detalls'] ?? '',
                'estat' => 'pendent',
            ]);
            $this->reportBroadcast->notificarUserReportCreat($report);

            return $report;
        }

        if ($contentType === 'post') {
            SocialPost::findOrFail($contentId);
        } else {
            SocialComment::findOrFail($contentId);
        }

        $report = Report::create([
            'usuari_id' => $userId,
            'tipus' => 'social_' . $contentType,
            'contingut' => $validated['reason'] ?? '',
            'post_id' => $contentId,
            'estat' => 'pendent',
        ]);
        $this->reportBroadcast->notificarReportCreat($report);

        return $report;
    }
}
