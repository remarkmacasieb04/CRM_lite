<?php

namespace App\Models;

use Database\Factories\ClientNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'user_id',
    'workspace_id',
    'content',
])]
class ClientNote extends Model
{
    /** @use HasFactory<ClientNoteFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $note): void {
            if ($note->workspace_id !== null) {
                return;
            }

            $note->workspace_id = $note->client_id !== null
                ? Client::query()->find($note->client_id)?->workspace_id
                : User::query()->find($note->user_id)?->current_workspace_id;
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
