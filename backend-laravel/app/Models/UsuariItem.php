<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model UsuariItem.
 * Correspon a la taula USUARIS_ITEMS definida a database/init.sql.
 * Pivot entre USUARIS i BOTIGA_ITEMS amb estat d'equipament i consum.
 */
class UsuariItem extends Model
{
    protected $table = 'usuaris_items';

    public $timestamps = false;

    protected $fillable = [
        'usuari_id',
        'item_id',
        'comprat_at',
        'equipat',
        'consumit_at',
    ];

    protected $casts = [
        'comprat_at' => 'datetime',
        'consumit_at' => 'datetime',
        'equipat' => 'boolean',
    ];

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Item del catàleg associat.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(BotigaItem::class, 'item_id');
    }

    /**
     * Usuari propietari.
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }
}
