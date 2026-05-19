<?php

namespace App\Services;

use App\Models\Report;
use App\Models\UserReport;
use Carbon\Carbon;

/**
 * Notifica als admins connectats (via Redis → Socket.io) quan hi ha canvis als reports.
 */
class AdminReportBroadcastService
{
    public function __construct(
        private RedisFeedbackService $feedbackService
    ) {}

    public function notificarReportCreat(Report $report): void
    {
        $report->load('usuari:id,nom');
        $this->publicar('CREATE', $this->filaDesDeReport($report));
    }

    public function notificarUserReportCreat(UserReport $report): void
    {
        $report->load(['usuari:id,nom', 'reportat:id,nom']);
        $this->publicar('CREATE', $this->filaDesDeUserReport($report));
    }

    public function notificarReportEliminat(int $id, string $table): void
    {
        $this->publicar('DELETE', ['id' => $id, 'table' => $table]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function publicar(string $action, array $data): void
    {
        $this->feedbackService->publicarPayload([
            'broadcast_admin' => true,
            'entity' => 'report',
            'action' => $action,
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function filaDesDeReport(Report $r): array
    {
        $createdAt = $r->created_at ? Carbon::parse($r->created_at) : null;

        return [
            'id' => $r->id,
            'table' => 'reports',
            'usuari' => $r->usuari ? $r->usuari->nom : 'Sistema',
            'tipus' => $r->tipus ?? '',
            'contingut' => $r->contingut ?? null,
            'post_id' => $r->post_id ?? null,
            'reported_user_nom' => null,
            'created_at' => $createdAt ? $createdAt->toIso8601String() : null,
            'data' => $createdAt ? $createdAt->diffForHumans() : 'Fa poc',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filaDesDeUserReport(UserReport $ur): array
    {
        $createdAt = $ur->created_at ? Carbon::parse($ur->created_at) : null;

        return [
            'id' => $ur->id,
            'table' => 'reports_usuari',
            'usuari' => $ur->usuari ? $ur->usuari->nom : 'Sistema',
            'tipus' => 'user',
            'contingut' => '[' . $ur->motiu . '] ' . ($ur->detalls ?: 'Sense detalls'),
            'post_id' => $ur->reportat_id,
            'reported_user_nom' => $ur->reportat ? $ur->reportat->nom : 'Desconegut',
            'created_at' => $createdAt ? $createdAt->toIso8601String() : null,
            'data' => $createdAt ? $createdAt->diffForHumans() : 'Fa poc',
        ];
    }
}
