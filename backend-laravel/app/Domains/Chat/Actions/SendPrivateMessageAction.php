<?php

declare(strict_types=1);

namespace App\Domains\Chat\Actions;

use App\Models\PrivateMessage;
use Illuminate\Http\Request;

/**
 * Envia un missatge privat (debug o autenticat).
 */
class SendPrivateMessageAction
{
    /**
     * @return array{status: int, body: mixed}
     */
    public function executar(Request $request, int $receiverId, bool $debug = false): array
    {
        try {
            $rules = [
                'contingut' => 'required|string|max:2000',
                'sender_id' => 'required|integer' . ($debug ? '' : '|min:1'),
            ];
            $validated = $request->validate($rules);
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'body' => ['error' => 'Validació fallida: ' . $e->getMessage()],
            ];
        }

        $message = PrivateMessage::create([
            'sender_id' => $validated['sender_id'],
            'receiver_id' => $receiverId,
            'contingut' => $validated['contingut'],
        ]);

        return ['status' => 201, 'body' => $message];
    }
}
