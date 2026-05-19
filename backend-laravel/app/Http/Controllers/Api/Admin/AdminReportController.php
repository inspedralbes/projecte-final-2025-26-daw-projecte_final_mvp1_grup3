<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\SocialComment;
use App\Models\SocialPost;
use App\Models\UserReport;
use App\Services\AdminReportBroadcastService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AdminReportController extends Controller
{
    public function __construct(
        private AdminReportBroadcastService $reportBroadcast
    ) {}

    /**
     * Reports d'usuaris (taula reports_usuari) per prohibir/desprohibir.
     */
    public function indexUsuaris(int $page = 1, int $perPage = 20): JsonResponse
    {
        $userReports = UserReport::with(['usuari:id,nom', 'reportat:id,nom,prohibit'])
            ->orderByDesc('created_at')
            ->get();

        $merged = [];
        foreach ($userReports as $ur) {
            $merged[] = $this->filaUserReport($ur);
        }

        return response()->json([
            'success' => true,
            'data' => $this->paginar($merged, $page, $perPage),
        ]);
    }

    /**
     * Reports de posts i comentaris per editar o eliminar contingut.
     */
    public function indexContingut(int $page = 1, int $perPage = 20): JsonResponse
    {
        $reports = Report::with('usuari:id,nom')
            ->whereIn('tipus', ['social_post', 'social_comment'])
            ->orderByDesc('created_at')
            ->get();

        $postIds = [];
        $commentIds = [];
        foreach ($reports as $r) {
            if ($r->tipus === 'social_post') {
                $postIds[] = $r->post_id;
            } elseif ($r->tipus === 'social_comment') {
                $commentIds[] = $r->post_id;
            }
        }

        $posts = SocialPost::withTrashed()
            ->with('user:id,nom')
            ->whereIn('id', array_unique($postIds))
            ->get()
            ->keyBy('id');

        $comments = SocialComment::with('user:id,nom')
            ->whereIn('id', array_unique($commentIds))
            ->get()
            ->keyBy('id');

        $merged = [];
        foreach ($reports as $r) {
            $merged[] = $this->filaContingutReport($r, $posts, $comments);
        }

        return response()->json([
            'success' => true,
            'data' => $this->paginar($merged, $page, $perPage),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{data: array<int, array<string, mixed>>, meta: array<string, int>}
     */
    private function paginar(array $items, int $page, int $perPage): array
    {
        usort($items, function ($a, $b) {
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        $total = count($items);
        $offset = ($page - 1) * $perPage;

        return [
            'data' => array_slice($items, $offset, $perPage),
            'meta' => [
                'current_page' => $page,
                'total' => $total,
                'per_page' => $perPage,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function filaUserReport(UserReport $ur): array
    {
        $createdAt = $ur->created_at ? Carbon::parse($ur->created_at) : null;

        return [
            'id' => $ur->id,
            'table' => 'reports_usuari',
            'categoria' => 'usuaris',
            'usuari' => $ur->usuari ? $ur->usuari->nom : 'Sistema',
            'tipus' => 'user',
            'contingut' => '[' . $ur->motiu . '] ' . ($ur->detalls ?: 'Sense detalls'),
            'post_id' => $ur->reportat_id,
            'reported_user_nom' => $ur->reportat ? $ur->reportat->nom : 'Desconegut',
            'reported_user_prohibit' => (bool) ($ur->reportat->prohibit ?? false),
            'created_at' => $createdAt ? $createdAt->toIso8601String() : null,
            'data' => $createdAt ? $createdAt->diffForHumans() : 'Fa poc',
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, SocialPost>  $posts
     * @param  \Illuminate\Support\Collection<int, SocialComment>  $comments
     * @return array<string, mixed>
     */
    private function filaContingutReport(Report $r, $posts, $comments): array
    {
        $createdAt = $r->created_at ? Carbon::parse($r->created_at) : null;
        $targetContingut = null;
        $targetAutor = null;
        $targetAutorId = null;
        $eliminat = false;

        if ($r->tipus === 'social_post' && isset($posts[$r->post_id])) {
            $post = $posts[$r->post_id];
            $targetContingut = $post->content;
            $targetAutor = $post->user ? $post->user->nom : null;
            $targetAutorId = $post->user_id;
            $eliminat = $post->trashed();
        } elseif ($r->tipus === 'social_comment' && isset($comments[$r->post_id])) {
            $comment = $comments[$r->post_id];
            $targetContingut = $comment->content;
            $targetAutor = $comment->user ? $comment->user->nom : null;
            $targetAutorId = $comment->user_id;
        }

        return [
            'id' => $r->id,
            'table' => 'reports',
            'categoria' => 'contingut',
            'usuari' => $r->usuari ? $r->usuari->nom : 'Sistema',
            'tipus' => $r->tipus ?? '',
            'contingut' => $r->contingut ?? null,
            'post_id' => $r->post_id ?? null,
            'target_contingut' => $targetContingut,
            'target_autor' => $targetAutor,
            'target_autor_id' => $targetAutorId,
            'eliminat' => $eliminat,
            'created_at' => $createdAt ? $createdAt->toIso8601String() : null,
            'data' => $createdAt ? $createdAt->diffForHumans() : 'Fa poc',
        ];
    }

    /**
     * Elimina (o resol) un report de la taula corresponent.
     */
    public function destroy(int $id): JsonResponse
    {
        $table = request()->query('table', 'reports');

        if ($table === 'reports_usuari') {
            $report = UserReport::findOrFail($id);
        } else {
            $report = Report::findOrFail($id);
        }

        $report->delete();

        $this->reportBroadcast->notificarReportEliminat($id, $table);

        return response()->json([
            'success' => true,
            'message' => 'Report eliminat correctament.'
        ]);
    }
}
