<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Domains\Social\Actions\CreateSocialReportAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

//================================ CONTROLLER ====================

/**
 * SocialReportController (thin).
 */
class SocialReportController extends Controller
{
    private CreateSocialReportAction $createReportAction;

    public function __construct(CreateSocialReportAction $createReportAction)
    {
        $this->createReportAction = $createReportAction;
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content_type' => 'required|in:post,comment,user',
            'content_id' => 'required|integer',
            'reason' => 'nullable|string|max:1000',
            'motiu' => 'nullable|string|max:255',
            'detalls' => 'nullable|string|max:1000',
        ]);

        $userId = (int) $request->user_id;
        $report = $this->createReportAction->executar($userId, $validated);

        return response()->json([
            'success' => true,
            'report' => [
                'id' => $report->id,
                'estat' => $report->estat ?? 'pendent',
            ],
        ], 201);
    }
}
