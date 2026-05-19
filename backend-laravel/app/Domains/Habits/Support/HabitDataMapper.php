<?php

declare(strict_types=1);


/**
 * Capa Laravel: HabitDataMapper.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace App\Domains\Habits\Support;

use App\Support\DificultatNormalizer;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Filtra i normalitza les dades d'un hàbit abans de persistir.
 */
class HabitDataMapper
{
    private HabitMetadataNormalizer $metadataNormalizer;

    public function __construct(HabitMetadataNormalizer $metadataNormalizer)
    {
        $this->metadataNormalizer = $metadataNormalizer;
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Filtra i normalitza les dades d'un hàbit.
     *
     * @param  array<string, mixed>  $habitData
     * @return array<string, mixed>
     */
    public function filtrarDadesHabit(array $habitData): array
    {
        $dades = [];

        if (isset($habitData['plantilla_id'])) {
            $dades['plantilla_id'] = $habitData['plantilla_id'];
        }

        if (isset($habitData['titol'])) {
            $dades['titol'] = $habitData['titol'];
        }

        if (isset($habitData['dificultat'])) {
            $normalitzada = DificultatNormalizer::normalitzar((string) $habitData['dificultat']);
            $dades['dificultat'] = $normalitzada !== '' ? $normalitzada : $habitData['dificultat'];
        }

        if (isset($habitData['frequencia_tipus'])) {
            $dades['frequencia_tipus'] = $habitData['frequencia_tipus'];
        }

        if (isset($habitData['dies_setmana'])) {
            $dades['dies_setmana'] = $this->normalitzarDiesSetmana($habitData['dies_setmana']);
        }

        if (isset($habitData['objectiu_vegades'])) {
            $dades['objectiu_vegades'] = $habitData['objectiu_vegades'];
        }

        if (isset($habitData['unitat'])) {
            $dades['unitat'] = $habitData['unitat'];
        }

        if (isset($habitData['categoria_id'])) {
            $dades['categoria_id'] = $habitData['categoria_id'];
        }

        if (isset($habitData['icona'])) {
            $dades['icona'] = $habitData['icona'];
        }

        if (isset($habitData['color'])) {
            $dades['color'] = $habitData['color'];
        }

        if (array_key_exists('moment_dia', $habitData)) {
            $dades['moment_dia'] = $this->metadataNormalizer->normalitzarMomentDia($habitData['moment_dia']);
        }

        if (array_key_exists('metadata', $habitData)) {
            $meta = $this->metadataNormalizer->normalitzarMetadata($habitData['metadata']);
            $columnaMetadata = $this->metadataNormalizer->obtenirColumnaMetadataHabits();
            if ($meta !== null && $columnaMetadata !== null) {
                $dades[$columnaMetadata] = $meta;
            }
        }

        return $dades;
    }

    /**
     * Normalitza dies_setmana a format Postgres array {t,f,...}.
     */
    public function normalitzarDiesSetmana(mixed $diesSetmana): string
    {
        if (is_array($diesSetmana)) {
            $valors = [];
            for ($i = 0; $i < count($diesSetmana); $i++) {
                if ($diesSetmana[$i]) {
                    $valors[] = 't';
                } else {
                    $valors[] = 'f';
                }
            }

            return '{' . implode(',', $valors) . '}';
        }
        if (is_string($diesSetmana)) {
            return $diesSetmana;
        }

        return '{t,t,t,t,t,t,t}';
    }
}
