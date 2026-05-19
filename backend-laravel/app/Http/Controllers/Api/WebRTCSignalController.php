<?php


/**
 * Capa Laravel: WebRTCSignalController.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace App\Http\Controllers\Api;

//================================ NAMESPACES / IMPORTS ============

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

//================================ MÈTODES / FUNCIONS ===========

class WebRTCSignalController extends Controller
{
    private function getRedis()
    {
        return Redis::connection();
    }

    public function handleSignal(Request $request): JsonResponse
    {
        $userId = $request->user_id ?? 0;
        
        if ($userId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $type = $request->input('type');
        $target = $request->input('target');
        $sdp = $request->input('sdp');
        $candidate = $request->input('candidate');

        $roomKey = 'webrtc_room_' . min($userId, $target) . '_' . max($userId, $target);
        $r = $this->getRedis();

        switch ($type) {
            case 'offer':
            case 'answer':
                $r->hset($roomKey, 'signal', json_encode([
                    'type' => $type,
                    'sdp' => $sdp,
                    'from' => $userId,
                ]));
                $r->expire($roomKey, 60);
                break;

            case 'ice-candidate':
                $signals = json_decode($r->hget($roomKey, 'signals') ?? '[]', true);
                $signals[] = ['candidate' => $candidate, 'from' => $userId];
                $r->hset($roomKey, 'signals', json_encode($signals));
                $r->expire($roomKey, 60);
                break;
        }

        return response()->json(['success' => true]);
    }

    public function getRoom(int $friendId, Request $request): JsonResponse
    {
        $userId = $request->user_id ?? 0;
        
        if ($userId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        $roomKey = 'webrtc_room_' . min($userId, $friendId) . '_' . max($userId, $friendId);
        $r = $this->getRedis();
        
        $signal = $r->hget($roomKey, 'signal');
        $signals = $r->hget($roomKey, 'signals');

        return response()->json([
            'signal' => $signal ? json_decode($signal, true) : null,
            'signals' => $signals ? json_decode($signals, true) : [],
        ]);
    }

    public function joinRoom(int $friendId, Request $request): JsonResponse
    {
        $userId = $request->user_id ?? 0;
        
        if ($userId === 0) {
            return response()->json(['error' => 'No autentificat'], 401);
        }

        return response()->json(['room_id' => $friendId]);
    }
}