<?php

declare(strict_types=1);

namespace App\Domains\Habits\Support;

//================================ NAMESPACES / IMPORTS ============

use App\Support\DificultatNormalizer;
use App\Support\GamificationConstants;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Calcula XP i monedes segons la dificultat de l'hàbit.
 */
class HabitRewardCalculator
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Calcula l'XP segons la dificultat de l'hàbit.
     * Fàcil: 100 XP, Mitjà: 250 XP, Difícil: 400 XP.
     */
    public function calcularXPSegonsDificultat(?string $dificultat): int
    {
        if ($dificultat === null || $dificultat === '') {
            return GamificationConstants::XP_DEFECTE;
        }

        $clau = DificultatNormalizer::normalitzar($dificultat);
        $mapXp = GamificationConstants::XP_PER_DIFICULTAT;

        if ($clau !== '' && array_key_exists($clau, $mapXp)) {
            return $mapXp[$clau];
        }

        return GamificationConstants::XP_DEFECTE;
    }

    /**
     * Calcula les monedes segons la dificultat de l'hàbit.
     */
    public function calcularMonedesSegonsDificultat(?string $dificultat): int
    {
        if ($dificultat === null || $dificultat === '') {
            return GamificationConstants::MONEDES_DEFECTE;
        }

        $clau = DificultatNormalizer::normalitzar($dificultat);
        $mapMonedes = GamificationConstants::MONEDES_PER_DIFICULTAT;

        if ($clau !== '' && array_key_exists($clau, $mapMonedes)) {
            return $mapMonedes[$clau];
        }

        return GamificationConstants::MONEDES_DEFECTE;
    }
}
