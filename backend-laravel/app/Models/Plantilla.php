<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model Plantilla.
 * Correspon a la taula PLANTILLES definida a database/init.sql.
 * Plantilles d'hàbits que els usuaris poden utilitzar.
 */
class Plantilla extends Model
{
    protected $table = 'plantilles';

    public $timestamps = false;

    protected $fillable = [
        'creador_id',
        'titol',
        'categoria',
        'es_publica',
    ];

    protected function casts(): array
    {
        return [
            'es_publica' => 'boolean',
        ];
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Usuari creador de la plantilla.
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    /**
     * Hàbits basats en aquesta plantilla.
     */
    public function habits(): HasMany
    {
        return $this->hasMany(Habit::class, 'plantilla_id');
    }
}
