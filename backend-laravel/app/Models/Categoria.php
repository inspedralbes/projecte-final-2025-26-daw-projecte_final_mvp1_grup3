<?php

//================================ NAMESPACES / IMPORTS ============
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    //================================ PROPIETATS / ATRIBUTS ==========
    /**
     * La taula associada al model.
     *
     * @var string
     */
    protected $table = 'CATEGORIES';

    /**
     * Indica si el model ha de tenir timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Els atributs que es poden assignar massivament.
     *
     * @var array
     */
    protected $fillable = [
        'nom',
    ];

    //================================ MÈTODES / FUNCIONS ===========

    //================================ RELACIONS ELOQUENT ===========
    /**
     * Obté les preguntes de registre associades a la categoria.
     *
     * @return HasMany
     */
    public function preguntes(): HasMany
    {
        return $this->hasMany(PreguntaRegistre::class, 'categoria_id');
    }
}
