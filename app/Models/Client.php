<?php

namespace App\Models;

use App\Enums\ClientStatus;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'company',
    'email',
    'phone',
    'status',
    'budget',
    'source',
    'last_contacted_at',
    'follow_up_at',
    'archived_at',
])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ClientStatus::class,
            'budget' => 'decimal:2',
            'last_contacted_at' => 'immutable_datetime',
            'follow_up_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ClientNote::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ClientActivity::class);
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function scopeOwnedBy(Builder $query, User $user): void
    {
        $query->whereBelongsTo($user);
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        $pattern = '%'.trim($search).'%';

        $query->where(function (Builder $searchQuery) use ($pattern): void {
            $searchQuery
                ->whereLike('name', $pattern)
                ->orWhereLike('company', $pattern)
                ->orWhereLike('email', $pattern)
                ->orWhereLike('phone', $pattern);
        });
    }

    public function scopeWithStatus(Builder $query, ClientStatus|string|null $status): void
    {
        if (blank($status)) {
            return;
        }

        $query->where('status', $status instanceof ClientStatus ? $status->value : $status);
    }

    public function scopeWithArchivedFilter(Builder $query, ?string $archived): void
    {
        if ($archived === 'only') {
            $query->whereNotNull('archived_at');

            return;
        }

        $query->whereNull('archived_at');
    }

    public function scopeRecentFirst(Builder $query): void
    {
        $query->orderByDesc('updated_at');
    }
}
