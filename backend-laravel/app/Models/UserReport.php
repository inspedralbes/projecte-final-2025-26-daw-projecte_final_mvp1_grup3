<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model UserReport.
 * Correspon a la taula REPORTS_USUARI definida a database/init.sql.
 * Reports directes d'usuaris a administradors.
 */
class UserReport extends Model
{
    protected $table = 'reports_usuari';

    public $timestamps = false;

    protected $fillable = [
        'usuari_id',
        'reportat_id',
        'motiu',
        'detalls',
        'estat',
    ];

    //================================ RELACIONS ELOQUENT ===========

    /**
     * Usuari que ha fet el report (denunciant).
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }

    /**
     * Usuari denunciat.
     */
    public function reportat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reportat_id');
    }
}
