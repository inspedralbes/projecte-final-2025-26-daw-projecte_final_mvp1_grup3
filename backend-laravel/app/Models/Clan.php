<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clan extends Model
{
    protected $table = 'clans';

    public $timestamps = false;

    protected $fillable = [
        'nom',
        'categoria_id',
        'es_public',
        'max_membres',
        'lider_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'es_public' => 'boolean',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(ClanMember::class, 'clan_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ClanRequest::class, 'clan_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ClanMessage::class, 'clan_id');
    }

    public function lider()
    {
        return $this->belongsTo(User::class, 'lider_id');
    }
}

class ClanMember extends Model
{
    protected $table = 'clan_members';

    public $timestamps = false;

    protected $fillable = [
        'clan_id',
        'usuari_id',
        'rol',
        'data_unio',
    ];

    public function usuari()
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }

    public function clan()
    {
        return $this->belongsTo(Clan::class, 'clan_id');
    }
}

class ClanRequest extends Model
{
    protected $table = 'clan_requests';

    public $timestamps = false;

    protected $fillable = [
        'clan_id',
        'usuari_id',
        'tipus',
        'estat',
        'invitador_id',
        'created_at',
    ];

    public function usuari()
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }

    public function clan()
    {
        return $this->belongsTo(Clan::class, 'clan_id');
    }
}

class ClanMessage extends Model
{
    protected $table = 'clan_messages';

    public $timestamps = false;

    protected $fillable = [
        'clan_id',
        'usuari_id',
        'contingut',
        'habit_id',
        'plantilla_id',
        'created_at',
    ];

    public function usuari()
    {
        return $this->belongsTo(User::class, 'usuari_id');
    }

    public function clan()
    {
        return $this->belongsTo(Clan::class, 'clan_id');
    }
}