<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'workspace_id',
    'name',
    'filters',
])]
class SavedClientView extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $view): void {
            if ($view->workspace_id !== null || $view->user_id === null) {
                return;
            }

            $view->workspace_id = User::query()
                ->find($view->user_id)
                ?->current_workspace_id;
        });
    }

    protected function casts(): array
    {
        return [
            'filters' => 'array',
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
}
