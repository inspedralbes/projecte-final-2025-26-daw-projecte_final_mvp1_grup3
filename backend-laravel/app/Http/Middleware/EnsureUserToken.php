<?php

namespace App\Http\Middleware;

//================================ NAMESPACES / IMPORTS ============

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Middleware que injecta user_id per defecte (MVP1: 1).
 */
class EnsureUserToken
{
    //================================ MÈTODES / FUNCIONS ===========

    public function handle(Request $request, Closure $next): Response
    {
        // A. Injectar usuari per defecte segons normes MVP1
        $request->merge(['user_id' => 1]);

        return $next($request);
    }
}
