<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SocialPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'social_posts';

    protected $fillable = [
        'user_id',
        'content',
        'habit_id',
        'plantilla_id',
        'attachments',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'attachments' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id');
    }

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class , 'habit_id');
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(Plantilla::class , 'plantilla_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SocialComment::class , 'post_id');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(SocialLike::class , 'likeable');
    }
}