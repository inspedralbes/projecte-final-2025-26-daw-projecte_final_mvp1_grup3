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
 * Middleware que verifica JWT amb role=admin i injecta admin_id al request.
 */
class EnsureAdminToken
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token invàlid o expirat'], 401);
        }

        $role = $payload->get('role');
        if ($role !== 'admin') {
            return response()->json(['message' => 'Accés no autoritzat'], 401);
        }

        $adminId = $payload->get('admin_id') ?? $payload->get('sub');
        if ($adminId === null) {
            return response()->json(['message' => 'Token invàlid'], 401);
        }

        $request->merge(['admin_id' => (int) $adminId]);

        return $next($request);
    }
}
