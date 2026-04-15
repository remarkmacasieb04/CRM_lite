<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'user_id',
    'workspace_id',
    'name',
    'slug',
])]
class Tag extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $tag): void {
            if ($tag->workspace_id !== null || $tag->user_id === null) {
                return;
            }

            $tag->workspace_id = User::query()
                ->find($tag->user_id)
                ?->current_workspace_id;
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class)->withTimestamps();
    }
}
