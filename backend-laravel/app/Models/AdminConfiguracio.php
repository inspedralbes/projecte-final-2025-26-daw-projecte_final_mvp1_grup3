<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model AdminConfiguracio.
 * Correspon a la taula ADMIN_CONFIGURACIO definida a database/insert.sql.
 * Paràmetres globals del sistema (XP per hàbit, monedes per missió, etc.).
 */
class AdminConfiguracio extends Model
{
    protected $table = 'admin_configuracio';

    public $timestamps = false;

    protected $fillable = [
        'clau',
        'valor',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    //================================ MÈTODES / FUNCIONS ===========
}
