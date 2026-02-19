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
    protected $table = 'habits';

    public $timestamps = false;

    protected $fillable = [
        'usuari_id',
        'plantilla_id',
        'titol',
        'dificultat',
        'frequencia_tipus',
        'dies_setmana',
        'objectiu_vegades',
    ];

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Usuari propietari de l'hàbit.
     */
    public function usuari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }

    /**
     * Plantilla d'origen de l'hàbit (si n'hi ha).
     */
    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(Plantilla::class, 'plantilla_id');
    }

    /**
     * Usuaris que tenen aquest hàbit assignat (via usuaris_habits).
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
     * Registres d'activitat d'aquest hàbit.
     */
    public function registresActivitat(): HasMany
    {
        return $this->hasMany(RegistreActivitat::class, 'habit_id');
    }
}
