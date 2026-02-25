<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model Habit.
 * Correspon a la taula HABITS definida a database/init.sql.
 * Hàbits associats als usuaris i opcionalment a una plantilla.
 */
class Habit extends Model
{
    /**
     * Taula associada al model.
     * @var string
     */
    protected $table = 'habits';

    /**
     * Indica si el model ha de tenir timestamps automàtics.
     * @var bool
     */
    public $timestamps = false;

    /**
     * Atributs assignables de forma massiva.
     * @var array
     */
    protected $fillable = [
        'usuari_id',
        'plantilla_id',
        'categoria_id',
        'titol',
        'dificultat',
        'frequencia_tipus',
        'dies_setmana',
        'objectiu_vegades',
        'icona',
        'color',
    ];

    //================================ MÈTODES / FUNCIONS ===========

    //================================ RELACIONS ELOQUENT ===========

    /**
     * Defineix la relació amb l'usuari propietari de l'hàbit.
     *
     * @return BelongsTo
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }

    /**
     * Defineix la relació amb les plantilles a les que pertany l'hàbit.
     *
     * @return BelongsToMany
     */
    public function plantilles(): BelongsToMany
    {
        return $this->belongsToMany(
            Plantilla::class,
            'plantilla_habit',
            'habit_id',
            'plantilla_id'
        );
    }

    /**
     * Categoria de l'hàbit.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Categoria de l'hàbit.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Defineix la relació amb els usuaris que tenen aquest hàbit assignat.
     *
     * @return BelongsToMany
     */
    public function usuaris(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'usuaris_habits',
            'habit_id',
            'usuari_id'
        )->withPivot('data_inici', 'actiu', 'objetiu_vegades_personalitzat');
    }

    /**
     * Defineix la relació amb els registres d'activitat de l'hàbit.
     *
     * @return HasMany
     */
    public function registresActivitat(): HasMany
    {
        return $this->hasMany(RegistreActivitat::class, 'habit_id');
    }
}
