<?php

namespace App\Http\Middleware;

//================================ NAMESPACES / IMPORTS ============

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Middleware que verifica JWT amb role=user i injecta user_id al request.
 */
class EnsureUserToken
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token invàlid o expirat'], 401);
        }

        $role = $payload->get('role');
        if ($role !== 'user') {
            return response()->json(['message' => 'Accés no autoritzat'], 401);
        }

        $userId = $payload->get('user_id') ?? $payload->get('sub');
        if ($userId === null) {
            return response()->json(['message' => 'Token invàlid'], 401);
        }

        $request->merge(['user_id' => (int) $userId]);

        return $next($request);
    }
}
