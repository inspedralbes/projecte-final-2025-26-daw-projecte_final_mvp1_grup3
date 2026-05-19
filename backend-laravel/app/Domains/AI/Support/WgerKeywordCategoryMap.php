<?php

namespace App\Domains\AI\Support;

/**
 * Mapa de paraules clau (es/ca/en) a IDs de categoria Wger.
 * Categories: 8=Arms, 9=Legs, 10=Abs, 11=Chest, 12=Back, 13=Shoulders, 14=Calves
 */
final class WgerKeywordCategoryMap
{
    /** @var array<string, int> */
    public const KEYWORDS = [
        'pit' => 11, 'pecho' => 11, 'chest' => 11,
        'pectoral' => 11, 'pectorals' => 11, 'pectorales' => 11,
        'banca' => 11, 'press' => 11, 'bench' => 11,
        'fondos' => 11, 'paralleles' => 11,
        'esquena' => 12, 'espalda' => 12, 'back' => 12,
        'dorsal' => 12, 'dorsals' => 12, 'lumbar' => 12,
        'remo' => 12, 'rem' => 12, 'dominada' => 12,
        'dominades' => 12, 'jalon' => 12, 'jalón' => 12,
        'pullup' => 12, 'pulldown' => 12,
        'bras' => 8, 'brazo' => 8, 'braços' => 8,
        'brazos' => 8, 'arms' => 8, 'arm' => 8,
        'bicep' => 8, 'biceps' => 8, 'tricep' => 8,
        'triceps' => 8, 'avantbraç' => 8, 'antebrazo' => 8,
        'curl' => 8, 'extensio' => 8, 'extension' => 8,
        'cama' => 9, 'cames' => 9, 'pierna' => 9,
        'piernas' => 9, 'legs' => 9, 'leg' => 9,
        'cuadricep' => 9, 'quadricep' => 9, 'femoral' => 9,
        'quads' => 9, 'sentadilla' => 9, 'sentadilles' => 9,
        'squat' => 9, 'llunada' => 9, 'zancada' => 9,
        'lunge' => 9, 'peso' => 9, 'deadlift' => 9,
        'espatlla' => 13, 'espatlles' => 13, 'hombro' => 13,
        'hombros' => 13, 'shoulder' => 13, 'shoulders' => 13,
        'deltoid' => 13, 'deltoides' => 13, 'militar' => 13,
        'elevacion' => 13, 'elevació' => 13,
        'abdomen' => 10, 'abdominal' => 10, 'abs' => 10,
        'core' => 10, 'ventre' => 10, 'barriga' => 10,
        'crunch' => 10, 'plancha' => 10, 'plank' => 10,
        'abdominals' => 10,
        'besson' => 14, 'bessons' => 14, 'gemelo' => 14,
        'gemelos' => 14, 'calves' => 14, 'calf' => 14,
        'pantorrilla' => 14, 'pantorrilles' => 14,
    ];

    public static function detectarCategoria(string $queryLower): ?int
    {
        $paraules = preg_split('/\s+/', $queryLower, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($paraules)) {
            $paraules = [$queryLower];
        }
        foreach ($paraules as $paraula) {
            $paraula = trim((string) $paraula, '.,;:!?');
            if (isset(self::KEYWORDS[$paraula])) {
                return self::KEYWORDS[$paraula];
            }
        }

        return null;
    }
}
