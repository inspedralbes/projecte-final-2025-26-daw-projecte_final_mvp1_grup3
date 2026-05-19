<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Normalitza valors de dificultat (facil / media / dificil) entre UI, BD i missions.
 */
final class DificultatNormalizer
{
    /** @var array<string, string> */
    private const ALIASES = [
        'facil' => 'facil',
        'fàcil' => 'facil',
        'easy' => 'facil',
        'mitja' => 'media',
        'mitjana' => 'media',
        'mitjà' => 'media',
        'media' => 'media',
        'medio' => 'media',
        'medium' => 'media',
        'dificil' => 'dificil',
        'difícil' => 'dificil',
        'hard' => 'dificil',
    ];

    /**
     * Retorna la clau canònica: facil, media o dificil (buit si no es reconeix).
     */
    public static function normalitzar(?string $dificultat): string
    {
        if ($dificultat === null) {
            return '';
        }

        $clau = strtolower(trim($dificultat));

        return self::ALIASES[$clau] ?? $clau;
    }

    /**
     * Valors possibles a habits.dificultat que equivalen a la dificultat demanada.
     *
     * @return list<string>
     */
    public static function valorsBdEquivalent(string $dificultat): array
    {
        $canonica = self::normalitzar($dificultat);
        if ($canonica === '') {
            return [];
        }

        $valors = [];
        foreach (self::ALIASES as $alias => $desti) {
            if ($desti === $canonica) {
                $valors[] = $alias;
            }
        }

        if (! in_array($canonica, $valors, true)) {
            $valors[] = $canonica;
        }

        return array_values(array_unique($valors));
    }
}
