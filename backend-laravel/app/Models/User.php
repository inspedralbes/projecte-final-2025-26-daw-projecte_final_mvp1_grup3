<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model User (Usuari).
 * Correspon a la taula USUARIS definida a database/init.sql.
 * Usuaris de l'aplicació amb gamificació (XP, monedes, nivell).
 * Implementa JWTSubject per autenticació JWT.
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'usuaris';

    public $timestamps = false;

    protected $casts = [
        'primer_login_correu_enviat_at' => 'datetime',
    ];

    protected $hidden = [
        'contrasenya_hash',
    ];

    protected $fillable = [
        'nom',
        'email',
        'google_id',
        'contrasenya_hash',
        'nivell',
        'xp_total',
        'xp_actual_nivel',
        'xp_objetivo_nivel',
        'monedes',
        'ruleta_ultima_tirada',
        'missio_diaria_id',
        'missio_completada',
        'ultim_reset_missio',
        'prohibit',
        'data_prohibicio',
        'motiu_prohibicio',
        'logros_showcase',
        'primer_login_correu_enviat_at',
        'monstre_tipus',
        'data_naixement_monstre',
    ];

    //================================ MÈTODES / FUNCIONS ===========

    /**
     * Identificador per al JWT (subject claim).
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Claims personalitzats per al JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return ['role' => 'user', 'user_id' => $this->getKey()];
    }

    /**
     * Missió diària assignada a l'usuari.
     */
    public function missioDiaria(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(MissioDiaria::class, 'missio_diaria_id');
    }

    /**
     * Logros i medalles obtinguts per l'usuari.
     */
    public function logros(): BelongsToMany
    {
        return $this->belongsToMany(
            LogroMedalla::class,
            'usuaris_logros',
            'usuari_id',
            'logro_id'
        )->withPivot('data_obtencio');
    }

    /**
     * Hàbits creats per l'usuari.
     */
    public function habits(): HasMany
    {
        return $this->hasMany(Habit::class, 'usuari_id');
    }

    /**
     * Hàbits assignats a l'usuari (via usuaris_habits).
     */
    public function habitsAssignats(): BelongsToMany
    {
        return $this->belongsToMany(
            Habit::class,
            'usuaris_habits',
            'usuari_id',
            'habit_id'
        )->withPivot('data_inici', 'actiu', 'objetiu_vegades_personalitzat');
    }

    /**
     * Ratxa de l'usuari.
     */
    public function ratxa(): HasMany
    {
        return $this->hasMany(Ratxa::class, 'usuari_id');
    }
}
