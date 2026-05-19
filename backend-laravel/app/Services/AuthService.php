<?php

namespace App\Services;

//================================ NAMESPACES / IMPORTS ============

/**
 * Alias de compatibilitat cap al domini User.
 *
 * @deprecated Utilitza App\Domains\User\Services\JwtCookieResponseService
 */
class AuthService extends \App\Domains\User\Services\JwtCookieResponseService
{
}
