<?php

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

//================================ PROPIETATS / ATRIBUTS ==========

/**
 * Model MissioDiaria.
 * Correspon a la taula MISSIOS_DIARIES definida a database/init.sql.
 * Defineix els tipus de missions diàries disponibles.
 */
class MissioDiaria extends Model
{
    use HasFactory;

    protected $table = 'missios_diaries';

    public $timestamps = false;

    protected $fillable = [
        'titol',
        'tipus_comprovacio',
        'parametres',
    ];

    protected function casts(): array
    {
        return [
            'parametres' => 'array',
        ];
    }

    //================================ MÈTODES / FUNCIONS ===========

    //================================ RELACIONS ELOQUENT ===========

    /**
     * Usuaris que tenen aquesta missió actualment assignada.
     */
    public function usuaris(): HasMany
    {
        return $this->hasMany(User::class, 'missio_diaria_id');
    }
}
