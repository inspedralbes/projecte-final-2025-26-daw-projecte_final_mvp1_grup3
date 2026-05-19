<?php

declare(strict_types=1);

namespace App\Domains\Habits\Support;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Normalitza i valida metadata i moment del dia dels hàbits.
 */
class HabitMetadataNormalizer
{
    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Compatibilitat d'esquema: alguns entorns antics guarden la columna com "metadada".
     */
    public function obtenirColumnaMetadataHabits(): ?string
    {
        static $columna = null;
        static $inicialitzat = false;

        if ($inicialitzat) {
            return $columna;
        }

        $inicialitzat = true;

        if (Schema::hasColumn('habits', 'metadata')) {
            $columna = 'metadata';

            return $columna;
        }

        if (Schema::hasColumn('habits', 'metadada')) {
            $columna = 'metadada';

            return $columna;
        }

        return null;
    }

    /**
     * Normalitza metadata externa i evita claus sensibles.
     *
     * @return array<string, string>|null
     */
    public function normalitzarMetadata(mixed $metadata): ?array
    {
        if ($metadata === null) {
            return null;
        }

        if (!is_array($metadata)) {
            return null;
        }

        $clausPermeses = ['api_id', 'titol', 'url_imatge', 'tipus_api'];
        $resultat = [];

        foreach ($clausPermeses as $clau) {
            if (!array_key_exists($clau, $metadata)) {
                continue;
            }
            $valor = $metadata[$clau];
            if ($valor === null) {
                $resultat[$clau] = '';
                continue;
            }
            if (!is_scalar($valor)) {
                $resultat[$clau] = '';
                continue;
            }
            $resultat[$clau] = mb_substr((string) $valor, 0, 500);
        }

        if (empty($resultat)) {
            return null;
        }

        return $resultat;
    }

    /**
     * Valida shape de metadata per accions CREATE/UPDATE.
     *
     * @param  array<string, mixed>  $habitData
     */
    public function validarShapeMetadata(array $habitData): bool
    {
        if (!array_key_exists('metadata', $habitData)) {
            return true;
        }

        $validator = Validator::make(
            ['metadata' => $habitData['metadata']],
            [
                'metadata' => 'nullable|array',
                'metadata.api_id' => 'nullable|string|max:500',
                'metadata.titol' => 'nullable|string|max:500',
                'metadata.url_imatge' => 'nullable|string|max:500',
                'metadata.tipus_api' => 'nullable|string|max:100',
            ]
        );

        return !$validator->fails();
    }

    /**
     * Normalitza el moment del dia (matí, tarda, nit, tot el dia).
     */
    public function normalitzarMomentDia(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return 'tot_dia';
        }

        $v = strtolower((string) $valor);
        $permesos = ['tot_dia', 'mati', 'tarda', 'nit'];

        if (!in_array($v, $permesos, true)) {
            return 'tot_dia';
        }

        return $v;
    }
}
