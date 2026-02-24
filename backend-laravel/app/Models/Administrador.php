<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model Administrador.
 * Correspon a la taula ADMINISTRADORS definida a database/init.sql.
 * Administradors del sistema amb accés exclusiu.
 * Sense auth per ara: accés via botó a la UI.
 */
class Administrador extends Model
{
    protected $table = 'administradors';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'email',
        'contrasenya_hash',
    ];

    protected $hidden = [
        'contrasenya_hash',
    ];

    protected function casts(): array
    {
        return [
            'data_creacio' => 'datetime',
        ];
    }

    //================================ MÈTODES / FUNCIONS ===========

    //================================ RELACIONS ELOQUENT ===========

    /**
     * Logs d'accions realitzades per l'administrador.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(AdminLog::class, 'administrador_id');
    }

    /**
     * Notificacions rebudes per l'administrador.
     */
    public function notificacions(): HasMany
    {
        return $this->hasMany(AdminNotificacio::class, 'administrador_id');
    }
}
