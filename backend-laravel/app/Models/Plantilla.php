<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model Plantilla.
 * Correspon a la taula PLANTILLES definida a database/init.sql.
 * Plantilles d'hàbits que els usuaris poden utilitzar.
 */
class Plantilla extends Model
{
    /**
     * Taula associada al model.
     * @var string
     */
    protected $table = 'plantilles';

    /**
     * Indica si el model ha de tenir timestamps automàtics.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atributs assignables de forma massiva.
     * @var array
     */
    protected $fillable = [
        'creador_id',
        'titol',
        'categoria',
        'es_publica',
    ];

    /**
     * Defineix el càsting d'atributs.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'es_publica' => 'boolean',
        ];
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Defineix la relació amb l'usuari creador de la plantilla.
     *
     * @return BelongsTo
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /**
     * Defineix la relació amb els hàbits associats a la plantilla.
     *
     * @return BelongsToMany
     */
    public function habits(): BelongsToMany
    {
        return $this->belongsToMany(
            Habit::class,
            'plantilla_habit',
            'plantilla_id',
            'habit_id'
        );
    }
}
