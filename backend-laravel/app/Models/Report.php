<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model Report.
 * Correspon a la taula REPORTS definida a database/init.sql.
 * Reports de contingut o usuaris per a moderació.
 */
class Report extends Model
{
    protected $table = 'reports';

    public $timestamps = false;

    protected $fillable = [
        'usuari_id',
        'tipus',
        'contingut',
        'post_id',
        'estat',
    ];

    //================================ MÈTODES / FUNCIONS ===========

    //================================ RELACIONS ELOQUENT ===========

    /**
     * Usuari que ha fet el report.
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }
}
