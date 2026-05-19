<?php

declare(strict_types=1);

namespace App\Domains\Onboarding\Support;

/**
 * Icones i colors per defecte segons categoria d'hàbit (onboarding).
 */
class OnboardingHabitCategoryDefaults
{
    public const ICONA_PER_CATEGORIA = [
        1 => '🏃',
        2 => '🥗',
        3 => '📚',
        4 => '📖',
        5 => '🧘',
        6 => '✨',
        7 => '🏠',
        8 => '🎨',
    ];

    public const COLOR_PER_CATEGORIA = [
        1 => '#10B981',
        2 => '#3B82F6',
        3 => '#F59E0B',
        4 => '#EF4444',
        5 => '#8B5CF6',
        6 => '#EC4899',
        7 => '#06B6D4',
        8 => '#1F2937',
    ];

    public static function icona(int $categoriaId): string
    {
        return self::ICONA_PER_CATEGORIA[$categoriaId] ?? '📝';
    }

    public static function color(int $categoriaId): string
    {
        return self::COLOR_PER_CATEGORIA[$categoriaId] ?? '#10B981';
    }
}
