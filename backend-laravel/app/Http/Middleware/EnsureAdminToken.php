<?php

namespace App\Http\Middleware;

//================================ NAMESPACES / IMPORTS ============

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Middleware que injecta admin_id per defecte (MVP1: 1).
 */
class EnsureAdminToken
{
    //================================ MÈTODES / FUNCIONS ===========

    public function handle(Request $request, Closure $next): Response
    {
        // A. Injectar administrador per defecte segons normes MVP1
        $request->merge(['admin_id' => 1]);

        return $next($request);
    }
}
