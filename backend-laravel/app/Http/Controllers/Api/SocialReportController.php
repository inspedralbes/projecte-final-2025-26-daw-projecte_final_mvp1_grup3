<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\UserReport;
use App\Models\SocialPost;
use App\Models\SocialComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content_type' => 'required|in:post,comment,user',
            'content_id' => 'required|integer',
            'reason' => 'nullable|string|max:1000',
            'motiu' => 'nullable|string|max:255',
            'detalls' => 'nullable|string|max:1000',
        ]);

        $contentType = $validated['content_type'];
        $contentId = $validated['content_id'];

        if ($contentType === 'user') {
            \App\Models\User::findOrFail($contentId);

            $report = UserReport::create([
                'usuari_id' => $request->user_id,
                'reportat_id' => $contentId,
                'motiu' => $validated['motiu'] ?? $validated['reason'] ?? 'Altres',
                'detalls' => $validated['detalls'] ?? '',
                'estat' => 'pendent',
            ]);
        } else {
            if ($contentType === 'post') {
                SocialPost::findOrFail($contentId);
            } else {
                SocialComment::findOrFail($contentId);
            }

            $report = Report::create([
                'usuari_id' => $request->user_id,
                'tipus' => 'social_' . $contentType,
                'contingut' => $validated['reason'] ?? '',
                'post_id' => $contentId,
                'estat' => 'pendent',
            ]);
        }

        return response()->json(['success' => true, 'report' => $report], 201);
    }
}