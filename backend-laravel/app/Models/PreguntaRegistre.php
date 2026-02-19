<?php

//================================ NAMESPACES / IMPORTS ============
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreguntaRegistre extends Model
{
    //================================ PROPIETATS / ATRIBUTS ==========
    /**
     * La taula associada al model.
     *
     * @var string
     */
    protected $table = 'PREGUNTES_REGISTRE';

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
        'categoria_id',
        'pregunta',
    ];

    //================================ MÈTODES / FUNCIONS ===========
    /**
     * Realitza una cerca a la columna 'pregunta' ignorant accents (PostgreSQL).
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $terme
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCercaSenseAccents($query, $terme)
    {
        // Ús d'unaccent per ignorar diacrítics en PostgreSQL
        return $query->whereRaw("unaccent(pregunta) ILIKE unaccent(?)", ["%$terme%"]);
    }

    //================================ RELACIONS ELOQUENT ===========
    /**
     * Obté la categoria a la qual pertany la pregunta.
     *
     * @return BelongsTo
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
