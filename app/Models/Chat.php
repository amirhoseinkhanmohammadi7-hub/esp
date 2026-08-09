<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Chat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'receiver_id',
        'message',
        'file_path',
        'file_type',
        'is_read',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * File type constants
     */
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_FILE = 'file';

    /**
     * Allowed MIME types for uploads
     */
    public const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'video/mp4',
        'video/webm',
        'audio/mpeg',
        'audio/wav',
        'audio/webm',
        'audio/ogg',
        'application/pdf',
    ];

    /**
     * Max file size in bytes (10MB)
     */
    public const MAX_FILE_SIZE = 10485760;

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the receiver of the message.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope a query to only include messages between two users.
     */
    public function scopeBetweenUsers(Builder $query, int $userId1, int $userId2): Builder
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where(function ($q2) use ($userId1, $userId2) {
                $q2->where('user_id', $userId1)->where('receiver_id', $userId2);
            })->orWhere(function ($q2) use ($userId1, $userId2) {
                $q2->where('user_id', $userId2)->where('receiver_id', $userId1);
            });
        });
    }

    /**
     * Scope a query to only include unread messages for a user.
     */
    public function scopeUnreadFor(Builder $query, int $userId): Builder
    {
        return $query->where('receiver_id', $userId)->where('is_read', false);
    }

    /**
     * Check if the message has a file attachment.
     */
    public function hasFile(): bool
    {
        return $this->file_path !== null;
    }

    /**
     * Get the full URL for the file path.
     */
    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? url('storage/' . $this->file_path) : null;
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead(): bool
    {
        if (!$this->is_read) {
            return $this->update(['is_read' => true]);
        }
        return false;
    }
}
