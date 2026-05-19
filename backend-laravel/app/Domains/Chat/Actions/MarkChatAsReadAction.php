<?php

declare(strict_types=1);

namespace App\Domains\Chat\Actions;

use App\Models\PrivateMessage;
use Illuminate\Http\Request;

/**
 * Marca com a llegits els missatges d'un amic cap a l'usuari.
 */
class MarkChatAsReadAction
{
    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function executar(Request $request, int $friendId, bool $debug = false): array
    {
        $userId = $debug
            ? (int) $request->input('user_id', 1)
            : (int) ($request->user_id ?? 0);

        if (! $debug && $userId === 0) {
            return ['status' => 401, 'body' => ['error' => 'No autentificat']];
        }

        PrivateMessage::where('sender_id', $friendId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return ['status' => 200, 'body' => ['success' => true]];
    }
}
