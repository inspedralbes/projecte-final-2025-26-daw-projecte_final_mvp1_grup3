<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model AdminNotificacio.
 * Correspon a la taula ADMIN_NOTIFICACIONS definida a database/insert.sql.
 * Notificacions per als administradors (usuaris nous, reportaments, etc.).
 */
class AdminNotificacio extends Model
{
    protected $table = 'admin_notificacions';

    public $timestamps = false;

    protected $fillable = [
        'administrador_id',
        'tipus',
        'titol',
        'descripcio',
        'data',
        'llegida',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'datetime',
            'llegida' => 'boolean',
            'metadata' => 'array',
        ];
    }

    //================================ MÈTODES / FUNCIONS ===========

    //================================ RELACIONS ELOQUENT ===========

    /**
     * Administrador destinatari de la notificació.
     */
    public function administrador(): BelongsTo
    {
        return $this->belongsTo(Administrador::class, 'administrador_id');
    }
}
