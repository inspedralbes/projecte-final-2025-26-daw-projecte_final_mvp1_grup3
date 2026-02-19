<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model LogroMedalla.
 * Correspon a la taula LOGROS_MEDALLES definida a database/init.sql.
 * Logros i medalles que els usuaris poden desbloquejar.
 */
class LogroMedalla extends Model
{
    protected $table = 'logros_medalles';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'descripcio',
        'tipus',
    ];

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Usuaris que han obtingut aquest logro.
     */
    public function usuaris(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'usuaris_logros',
            'logro_id',
            'usuari_id'
        )->withPivot('data_obtencio');
    }
}
