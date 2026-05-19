<?php

declare(strict_types=1);

namespace App\Domains\User\Actions;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Actualitza els logros destacats al perfil (màxim 3).
 */
class UpdateShowcaseAction
{
    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function executar(Request $request): array
    {
        try {
            $user = User::findOrFail($request->user_id);
            $logroIds = $request->input('logros', []);

            if (! is_array($logroIds)) {
                return ['status' => 400, 'body' => ['error' => 'Invalid format']];
            }

            if (count($logroIds) > 3) {
                return ['status' => 400, 'body' => ['error' => 'Maximum 3 logros']];
            }

            $user->logros_showcase = '{' . implode(',', $logroIds) . '}';
            $user->save();

            return [
                'status' => 200,
                'body' => ['success' => true, 'logros_showcase' => $logroIds],
            ];
        } catch (\Exception $e) {
            return ['status' => 500, 'body' => ['error' => $e->getMessage()]];
        }
    }
}
