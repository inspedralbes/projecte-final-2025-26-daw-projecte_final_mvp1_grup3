<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model UsuariHabit.
 * Correspon a la taula USUARIS_HABITS definida a database/init.sql.
 * Taula pivot que vincula usuaris amb hàbits (relació N:M).
 */
class UsuariHabit extends Model
{
    protected $table = 'usuaris_habits';

    public $timestamps = false;

    protected $fillable = [
        'usuari_id',
        'habit_id',
        'data_inici',
        'actiu',
        'objetiu_vegades_personalitzat',
    ];

    protected function casts(): array
    {
        return [
            'data_inici' => 'date',
            'actiu' => 'boolean',
        ];
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Usuari de la relació.
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }

    /**
     * Hàbit de la relació.
     */
    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class, 'habit_id');
    }
}
