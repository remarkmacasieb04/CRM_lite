<?php

namespace App\Models;

use App\Enums\ClientStatus;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function attachments(): HasMany
    {
        return $this->hasMany(ClientAttachment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
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

    public function scopeWithTagSlug(Builder $query, User $user, ?string $tagSlug): void
    {
        if (blank($tagSlug)) {
            return;
        }

        $query->whereHas('tags', function (Builder $tagQuery) use ($user, $tagSlug): void {
            $tagQuery
                ->whereBelongsTo($user)
                ->where('slug', $tagSlug);
        });
    }

    public function scopeWithFollowUpFilter(Builder $query, ?string $followUp): void
    {
        if ($followUp === 'overdue') {
            $query
                ->whereNotNull('follow_up_at')
                ->where('follow_up_at', '<', now()->startOfDay());

            return;
        }

        if ($followUp === 'week') {
            $query
                ->whereNotNull('follow_up_at')
                ->whereBetween('follow_up_at', [now()->startOfDay(), now()->addDays(7)->endOfDay()]);
        }
    }

    public function scopeWithStaleContactFilter(Builder $query, ?string $stale): void
    {
        if ($stale !== 'yes') {
            return;
        }

        $query->where(function (Builder $staleQuery): void {
            $staleQuery
                ->whereNull('last_contacted_at')
                ->orWhere('last_contacted_at', '<', now()->subDays(14)->startOfDay());
        });
    }
}
