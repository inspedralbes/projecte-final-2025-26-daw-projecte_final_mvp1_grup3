<?php

declare(strict_types=1);

namespace App\Domains\User\Support;

use Illuminate\Http\Request;

/**
 * Extreu el JWT de la petició prioritzant Authorization Bearer sobre la cookie.
 */
class JwtRequestTokenSupport
{
    public static function fromRequest(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        if (is_string($authHeader) && str_starts_with($authHeader, 'Bearer ')) {
            $token = trim(substr($authHeader, 7));
            if ($token !== '') {
                return $token;
            }
        }

        $cookieNom = (string) config('jwt.cookie', 'loopy_token');
        $cookieToken = $request->cookie($cookieNom);
        if (is_string($cookieToken) && $cookieToken !== '') {
            return $cookieToken;
        }

        return null;
    }
}
