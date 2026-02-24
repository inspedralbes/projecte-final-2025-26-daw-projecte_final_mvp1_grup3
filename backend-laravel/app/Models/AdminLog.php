<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model AdminLog.
 * Correspon a la taula ADMIN_LOGS definida a database/insert.sql.
 * Registra accions d'administració amb estat abans/després per auditoria.
 */
class AdminLog extends Model
{
    protected $table = 'admin_logs';

    public $timestamps = false;

    protected $fillable = [
        'administrador_id',
        'accio',
        'detall',
        'abans',
        'despres',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'abans' => 'array',
            'despres' => 'array',
            'created_at' => 'datetime',
        ];
    }

    //================================ MÈTODES / FUNCIONS ===========

    //================================ RELACIONS ELOQUENT ===========

    /**
     * Administrador que ha realitzat l'acció.
     */
    public function administrador(): BelongsTo
    {
        return $this->belongsTo(Administrador::class, 'administrador_id');
    }
}
