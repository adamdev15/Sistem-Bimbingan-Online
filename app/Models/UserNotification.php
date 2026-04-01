<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id',
        'sender_user_id',
        'type',
        'title',
        'body',
        'subject_type',
        'subject_id',
        'action_route',
        'action_params',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'action_params' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function markRead(): void
    {
        if ($this->read_at === null) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }
}
