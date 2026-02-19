<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model Ratxa.
 * Correspon a la taula RATXES definida a database/init.sql.
 * Seguiment de ratxes (dies consecutius) per usuari.
 */
class Ratxa extends Model
{
    protected $table = 'ratxes';

    public $timestamps = false;

    protected $fillable = [
        'usuari_id',
        'ratxa_actual',
        'ratxa_maxima',
        'ultima_data',
    ];

    protected function casts(): array
    {
        return [
            'ultima_data' => 'date',
        ];
    }

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Usuari de la ratxa.
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }
}
