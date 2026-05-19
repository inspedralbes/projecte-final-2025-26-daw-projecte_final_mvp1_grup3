<?php

declare(strict_types=1);

namespace App\Domains\Chat\Queries;

use App\Models\PrivateMessage;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Historial de missatges entre dos usuaris.
 */
class GetChatHistoryQuery
{
    /**
     * @return array{status: int, body: mixed}
     */
    public function executar(Request $request, int $friendId, bool $debug = false): array
    {
        if ($debug) {
            $userId = (int) $request->input('user_id', 1);
            $messages = $this->missatgesEntre($userId, $friendId)->orderBy('created_at', 'asc')->get();

            return ['status' => 200, 'body' => ['data' => $messages]];
        }

        $userId = (int) ($request->user_id ?? 0);
        if ($userId === 0) {
            return ['status' => 401, 'body' => ['error' => 'No autentificat']];
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $this->missatgesEntre($userId, $friendId)
            ->orderBy('created_at', 'asc')
            ->paginate(50);

        return ['status' => 200, 'body' => $paginator];
    }

    private function missatgesEntre(int $userId, int $friendId)
    {
        return PrivateMessage::where(function ($query) use ($userId, $friendId) {
            $query->where(function ($q) use ($userId, $friendId) {
                $q->where('sender_id', $userId)->where('receiver_id', $friendId);
            })->orWhere(function ($q) use ($userId, $friendId) {
                $q->where('sender_id', $friendId)->where('receiver_id', $userId);
            });
        });
    }
}
