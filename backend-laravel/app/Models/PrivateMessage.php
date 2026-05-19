<?php


/**
 * Capa Laravel: PrivateMessage.
 * Comentaris: agents/backend/AgentLaravel.md
 */

namespace App\Models;

//================================ NAMESPACES / IMPORTS ============

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

//================================ MÈTODES / FUNCIONS ===========

class PrivateMessage extends Model
{
    use HasFactory;

    protected $table = 'private_messages';

    public $timestamps = false;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'contingut',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}