<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\WorkspaceMemberRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'current_workspace_id', 'receives_follow_up_reminders', 'last_follow_up_digest_sent_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'role' => UserRole::class,
            'password' => 'hashed',
            'current_workspace_id' => 'integer',
            'receives_follow_up_reminders' => 'boolean',
            'last_follow_up_digest_sent_at' => 'date',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (self $user): void {
            if (! Schema::hasTable('workspaces') || ! Schema::hasTable('workspace_user')) {
                return;
            }

            if ($user->workspaces()->exists()) {
                return;
            }

            $workspace = Workspace::query()->create([
                'owner_user_id' => $user->id,
                'name' => "{$user->name}'s Workspace",
                'slug' => Str::slug($user->name).'-'.$user->id,
                'is_personal' => true,
            ]);

            $user->workspaces()->attach($workspace->id, [
                'role' => WorkspaceMemberRole::Owner->value,
            ]);

            $user->forceFill([
                'current_workspace_id' => $workspace->id,
            ])->saveQuietly();
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function currentWorkspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'current_workspace_id');
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function clientNotes(): HasMany
    {
        return $this->hasMany(ClientNote::class);
    }

    public function clientActivities(): HasMany
    {
        return $this->hasMany(ClientActivity::class);
    }

    public function clientAttachments(): HasMany
    {
        return $this->hasMany(ClientAttachment::class);
    }

    public function clientCommunications(): HasMany
    {
        return $this->hasMany(ClientCommunication::class);
    }

    public function clientDocuments(): HasMany
    {
        return $this->hasMany(ClientDocument::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function savedClientViews(): HasMany
    {
        return $this->hasMany(SavedClientView::class);
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by_user_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to_user_id');
    }

    public function createdPortalShares(): HasMany
    {
        return $this->hasMany(ClientPortalShare::class, 'created_by_user_id');
    }

    public function resolveCurrentWorkspace(): ?Workspace
    {
        $this->loadMissing(['currentWorkspace', 'workspaces']);

        if ($this->currentWorkspace !== null) {
            return $this->currentWorkspace;
        }

        $workspace = $this->workspaces()->orderBy('workspaces.name')->first();

        if ($workspace === null) {
            return null;
        }

        $this->forceFill([
            'current_workspace_id' => $workspace->id,
        ])->saveQuietly();

        $this->setRelation('currentWorkspace', $workspace);

        return $workspace;
    }

    public function workspaceRole(?Workspace $workspace = null): ?WorkspaceMemberRole
    {
        $workspace ??= $this->resolveCurrentWorkspace();

        if ($workspace === null) {
            return null;
        }

        $membership = $this->workspaces()
            ->where('workspaces.id', $workspace->id)
            ->first();

        $role = $membership?->pivot?->role;

        return is_string($role) ? WorkspaceMemberRole::from($role) : null;
    }

    public function belongsToWorkspace(Workspace $workspace): bool
    {
        return $this->workspaces()
            ->where('workspaces.id', $workspace->id)
            ->exists();
    }
}
