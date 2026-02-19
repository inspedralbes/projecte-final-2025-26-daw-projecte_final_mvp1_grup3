<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model RegistreActivitat.
 * Correspon a la taula REGISTRE_ACTIVITAT definida a database/init.sql.
 * Registre de completació d'hàbits amb XP guanyada.
 */
class RegistreActivitat extends Model
{
    protected $table = 'registre_activitat';

    public $timestamps = false;

    protected $fillable = [
        'habit_id',
        'data',
        'acabado',
        'xp_guanyada',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'datetime',
            'acabado' => 'boolean',
        ];
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Hàbit del registre.
     */
    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class, 'habit_id');
    }
}
