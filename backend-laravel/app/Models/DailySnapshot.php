<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model DailySnapshot.
 * Correspon a la taula DAILY_SNAPSHOTS.
 * Snapshot diari immutable de l'estat de l'usuari.
 */
class DailySnapshot extends Model
{
    protected $table = 'daily_snapshots';

    public $timestamps = false;

    protected $fillable = [
        'usuari_id',
        'data',
        'mascota_json',
        'habits_json',
        'economia_json',
        'created_at',
    ];

    protected $casts = [
        'mascota_json' => 'array',
        'habits_json' => 'array',
        'economia_json' => 'array',
        'data' => 'date',
    ];

    //================================ MÈTODES / FUNCIONS ===========

    //================================ RELACIONS ELOQUENT ===========

    /**
     * Usuari propietari del snapshot.
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }
}
