<?php

declare(strict_types=1);

namespace App\Domains\Habits\Support;

//================================ NAMESPACES / IMPORTS ============

use App\Models\User;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Configuració i càlcul de nivells d'usuari a partir de XP.
 */
class UserLevelCalculator
{
    public const XP_BASE_NIVELL = 1000;

    public const MULTIPLICADOR_NIVELL = 1.2;

    public const BONUS_MONEDES_NIVELL = 10;

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Calcula l'objectiu d'XP per al nivell indicat.
     */
    public function calcularObjectiuNivell(int $nivell): int
    {
        if ($nivell < 1) {
            $nivell = 1;
        }
        $objectiu = self::XP_BASE_NIVELL * pow(self::MULTIPLICADOR_NIVELL, $nivell - 1);

        return (int) round($objectiu);
    }

    /**
     * Normalitza nivells a partir del total d'XP si cal.
     *
     * @return array{nivell:int,xp_actual_nivel:int,xp_objetivo_nivel:int}
     */
    public function normalitzarNivell(User $usuari): array
    {
        $nivell = isset($usuari->nivell) ? (int) $usuari->nivell : 1;
        $xpActual = isset($usuari->xp_actual_nivel) ? (int) $usuari->xp_actual_nivel : 0;
        $xpObjectiu = isset($usuari->xp_objetivo_nivel) ? (int) $usuari->xp_objetivo_nivel : 0;

        if ($xpObjectiu <= 0) {
            $xpObjectiu = $this->calcularObjectiuNivell($nivell);
        }

        if ($xpActual < 0 || $xpActual >= $xpObjectiu) {
            $xpTotal = isset($usuari->xp_total) ? (int) $usuari->xp_total : 0;
            $nivell = 1;
            $xpObjectiu = $this->calcularObjectiuNivell($nivell);
            $restant = $xpTotal;
            while ($restant >= $xpObjectiu) {
                $restant -= $xpObjectiu;
                $nivell++;
                $xpObjectiu = $this->calcularObjectiuNivell($nivell);
            }
            $xpActual = $restant;
        }

        return [
            'nivell' => $nivell,
            'xp_actual_nivel' => $xpActual,
            'xp_objetivo_nivel' => $xpObjectiu,
        ];
    }

    /**
     * Aplica XP i calcula canvi de nivell.
     *
     * @return array{xp_total:int,nivell:int,xp_actual_nivel:int,xp_objetivo_nivel:int,level_up:bool,bonus_monedes:int}
     */
    public function aplicarXpINivell(User $usuari, int $xpAfegida): array
    {
        $nivellData = $this->normalitzarNivell($usuari);
        $nivell = $nivellData['nivell'];
        $xpActual = $nivellData['xp_actual_nivel'];
        $xpObjectiu = $nivellData['xp_objetivo_nivel'];

        $xpActual += $xpAfegida;
        $levelUp = false;
        $bonusMonedes = 0;

        while ($xpActual >= $xpObjectiu) {
            $xpActual -= $xpObjectiu;
            $nivell++;
            $levelUp = true;
            $bonusMonedes += self::BONUS_MONEDES_NIVELL;
            $xpObjectiu = $this->calcularObjectiuNivell($nivell);
        }

        $xpTotal = isset($usuari->xp_total) ? (int) $usuari->xp_total : 0;
        $xpTotal += $xpAfegida;

        return [
            'xp_total' => $xpTotal,
            'nivell' => $nivell,
            'xp_actual_nivel' => $xpActual,
            'xp_objetivo_nivel' => $xpObjectiu,
            'level_up' => $levelUp,
            'bonus_monedes' => $bonusMonedes,
        ];
    }

    /**
     * Recalcula nivell, xp_actual_nivel i xp_objetivo_nivel a partir del xp_total.
     *
     * @return array{nivell:int,xp_actual_nivel:int,xp_objetivo_nivel:int}
     */
    public function recalcularNivellDesDeXpTotal(int $xpTotal): array
    {
        if ($xpTotal <= 0) {
            return [
                'nivell' => 1,
                'xp_actual_nivel' => 0,
                'xp_objetivo_nivel' => self::XP_BASE_NIVELL,
            ];
        }
        $nivell = 1;
        $xpObjectiu = $this->calcularObjectiuNivell($nivell);
        $restant = $xpTotal;
        while ($restant >= $xpObjectiu) {
            $restant -= $xpObjectiu;
            $nivell++;
            $xpObjectiu = $this->calcularObjectiuNivell($nivell);
        }

        return [
            'nivell' => $nivell,
            'xp_actual_nivel' => $restant,
            'xp_objetivo_nivel' => $xpObjectiu,
        ];
    }

    /**
     * Construeix payload xp_update amb ratxes per feedback.
     *
     * @return array<string, int>
     */
    public function construirXpUpdateAmbRatxa(User $usuari, int $ratxaActual, int $ratxaMaxima): array
    {
        $nivell = isset($usuari->nivell) ? (int) $usuari->nivell : 1;
        $xpActualNivell = isset($usuari->xp_actual_nivel) ? (int) $usuari->xp_actual_nivel : 0;
        $xpObjectiuNivell = isset($usuari->xp_objetivo_nivel)
            ? (int) $usuari->xp_objetivo_nivel
            : self::XP_BASE_NIVELL;
        $monedes = isset($usuari->monedes) ? (int) $usuari->monedes : 0;

        return [
            'xp_total' => (int) $usuari->xp_total,
            'nivell' => $nivell,
            'xp_actual_nivel' => $xpActualNivell,
            'xp_objetivo_nivel' => $xpObjectiuNivell,
            'ratxa_actual' => $ratxaActual,
            'ratxa_maxima' => $ratxaMaxima,
            'monedes' => $monedes,
        ];
    }
}
