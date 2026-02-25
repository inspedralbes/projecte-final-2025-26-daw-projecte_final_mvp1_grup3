<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model Administrador.
 * Correspon a la taula ADMINISTRADORS definida a database/init.sql.
 * Administradors del sistema amb accés exclusiu.
 * Implementa JWTSubject per autenticació JWT.
 */
class Administrador extends Model implements JWTSubject
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

    /**
     * Identificador per al JWT (subject claim).
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Claims personalitzats per al JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return ['role' => 'admin', 'admin_id' => $this->getKey()];
    }

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
