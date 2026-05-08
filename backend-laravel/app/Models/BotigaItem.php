<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model BotigaItem.
 * Correspon a la taula BOTIGA_ITEMS definida a database/init.sql.
 * Catàleg d'objectes de la tenda Loopy (skins permanents i consumibles).
 */
class BotigaItem extends Model
{
    protected $table = 'botiga_items';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'descripcio',
        'preu',
        'tipus',
        'imatge',
        'metadata',
        'actiu',
    ];

    protected $casts = [
        'metadata' => 'array',
        'actiu' => 'boolean',
        'preu' => 'integer',
    ];

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Compres registrades d'aquest item per part dels usuaris.
     */
    public function compres(): HasMany
    {
        return $this->hasMany(UsuariItem::class, 'item_id');
    }
}
