<?php

namespace App\Models;

use App\Enums\ClientActivityType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'workspace_id',
    'client_id',
    'type',
    'description',
    'properties',
])]
class ClientActivity extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $activity): void {
            if ($activity->workspace_id !== null) {
                return;
            }

            $activity->workspace_id = $activity->client_id !== null
                ? Client::query()->find($activity->client_id)?->workspace_id
                : User::query()->find($activity->user_id)?->current_workspace_id;
        });
    }

    protected function casts(): array
    {
        return [
            'type' => ClientActivityType::class,
            'properties' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
