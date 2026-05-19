<?php


/**
 * Capa Laravel: SocialLike.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

//================================ MÈTODES / FUNCIONS ===========

class SocialLike extends Model
{
    protected $table = 'social_likes';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'likeable_id',
        'likeable_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}