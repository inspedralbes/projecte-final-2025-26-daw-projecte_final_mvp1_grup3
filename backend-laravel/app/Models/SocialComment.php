<?php


/**
 * Capa Laravel: SocialComment.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

//================================ MÈTODES / FUNCIONS ===========

class SocialComment extends Model
{
    use HasFactory;

    protected $table = 'social_comments';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'depth_level',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class , 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SocialComment::class , 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SocialComment::class , 'parent_id');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(SocialLike::class , 'likeable');
    }
}