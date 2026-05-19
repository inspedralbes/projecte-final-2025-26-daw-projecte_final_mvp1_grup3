<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model Habit.
 * Correspon a la taula HABITS definida a database/init.sql.
 * Hàbits associats als usuaris i opcionalment a una plantilla.
 */
class Habit extends Model
{
    protected $table = 'habits';

    public $timestamps = false;

    protected $fillable = [
        'usuari_id',
        'plantilla_id',
        'categoria_id',
        'titol',
        'dificultat',
        'frequencia_tipus',
        'dies_setmana',
        'objectiu_vegades',
        'unitat',
        'icona',
        'color',
        'moment_dia',
        'metadata',
        'metadada',
    ];

    protected $casts = [
        'metadata' => 'array',
        'metadada' => 'array',
    ];

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Formulari admin: "1,2,3" (1 = dilluns … 7 = diumenge) → literal PostgreSQL BOOLEAN[7].
     */
    public static function diesSetmanaCsvAJsonPg(?string $csv): string
    {
        $csv = $csv !== null ? trim($csv) : '';
        $booleanDaysArr = array_fill(0, 7, 'f');
        if ($csv === '') {
            for ($i = 0; $i < 7; $i++) {
                $booleanDaysArr[$i] = 't';
            }
        } else {
            $parts = preg_split('/\s*,\s*/', $csv, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($parts as $part) {
                $dayIndex = (int) trim($part);
                if ($dayIndex >= 1 && $dayIndex <= 7) {
                    $booleanDaysArr[$dayIndex - 1] = 't';
                }
            }
        }

        return '{'.implode(',', $booleanDaysArr).'}';
    }

    /**
     * Valor de columna (array / string PG) → CSV per al formulari admin.
     */
    public static function diesSetmanaPgACsv($diesSetmana): string
    {
        $bools = self::normalitzaDiesSetmanaABooleans($diesSetmana);
        if (count($bools) !== 7) {
            return '1,2,3,4,5,6,7';
        }
        $out = [];
        for ($i = 0; $i < 7; $i++) {
            if (! empty($bools[$i])) {
                $out[] = (string) ($i + 1);
            }
        }

        return count($out) > 0 ? implode(',', $out) : '1,2,3,4,5,6,7';
    }

    /**
     * @return array<int, bool>
     */
    private static function normalitzaDiesSetmanaABooleans($diesSetmana): array
    {
        if (is_array($diesSetmana)) {
            $out = [];
            for ($i = 0; $i < 7; $i++) {
                $out[$i] = isset($diesSetmana[$i]) && filter_var($diesSetmana[$i], FILTER_VALIDATE_BOOLEAN);
            }

            return $out;
        }
        if (is_string($diesSetmana)) {
            $net = str_replace(['{', '}'], '', $diesSetmana);
            if ($net === '') {
                return [];
            }
            $parts = explode(',', $net);
            $out = [];
            foreach ($parts as $idx => $val) {
                if ($idx >= 7) {
                    break;
                }
                $v = strtolower(trim((string) $val));
                $out[$idx] = $v === 't' || $v === 'true' || $v === '1';
            }
            while (count($out) < 7) {
                $out[] = false;
            }

            return $out;
        }

        return [];
    }

    //================================ RELACIONS ELOQUENT ===========

    /**
     * Defineix la relació amb l'usuari propietari de l'hàbit.
     *
     * @return BelongsTo
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(User::class , 'usuari_id');
    }

    /**
     * Defineix la relació amb la plantilla origen (plantilla_id).
     *
     * @return BelongsTo
     */
    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(Plantilla::class , 'plantilla_id');
    }

    /**
     * Defineix la relació amb les plantilles a les que pertany l'hàbit.
     *
     * @return BelongsToMany
     */
    public function plantilles(): BelongsToMany
    {
        return $this->belongsToMany(
            Plantilla::class ,
            'plantilla_habit',
            'habit_id',
            'plantilla_id'
        );
    }

    /**
     * Categoria de l'hàbit.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class , 'categoria_id');
    }

    /**
     * Defineix la relació amb els usuaris que tenen aquest hàbit assignat.
     *
     * @return BelongsToMany
     */
    public function usuaris(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class ,
            'usuaris_habits',
            'habit_id',
            'usuari_id'
        )->withPivot('data_inici', 'actiu', 'objetiu_vegades_personalitzat');
    }

    /**
     * Defineix la relació amb els registres d'activitat de l'hàbit.
     *
     * @return HasMany
     */
    public function registresActivitat(): HasMany
    {
        return $this->hasMany(RegistreActivitat::class , 'habit_id');
    }

    /**
     * Compatibilitat antiga: alguns entorns conserven la columna "metadada".
     */
    public function getMetadataAttribute($value): ?array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!empty($value) && is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }

        $legacy = $this->attributes['metadada'] ?? null;
        if (is_array($legacy)) {
            return $legacy;
        }
        if (!empty($legacy) && is_string($legacy)) {
            $decodedLegacy = json_decode($legacy, true);
            return is_array($decodedLegacy) ? $decodedLegacy : null;
        }

        return null;
    }
}