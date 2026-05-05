<?php

namespace App\Support;

/**
 * Constants compartides de gamificació.
 * Evita duplicar mapes de dificultat entre serveis.
 */
final class GamificationConstants
{
    public const XP_PER_DIFICULTAT = [
        'facil' => 100,
        'media' => 250,
        'dificil' => 400,
    ];

    public const MONEDES_PER_DIFICULTAT = [
        'facil' => 2,
        'media' => 5,
        'dificil' => 10,
    ];

    public const XP_DEFECTE = 100;
    public const MONEDES_DEFECTE = 2;
}

