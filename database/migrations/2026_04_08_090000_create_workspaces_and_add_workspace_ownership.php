<?php

use App\Enums\WorkspaceMemberRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_personal')->default(false);
            $table->timestamps();

            $table->index(['owner_user_id', 'is_personal']);
        });

        Schema::create('workspace_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20)->default(WorkspaceMemberRole::Member->value);
            $table->timestamps();

            $table->unique(['workspace_id', 'user_id']);
            $table->index(['user_id', 'role']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('current_workspace_id')->nullable()->after('role')->constrained('workspaces')->nullOnDelete();
        });

        Schema::table('clients', function (Blueprint $table): void {
            $table->foreignId('workspace_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'follow_up_at']);
            $table->index(['workspace_id', 'archived_at']);
            $table->index(['workspace_id', 'updated_at']);
            $table->index(['workspace_id', 'email']);
        });

        Schema::table('client_notes', function (Blueprint $table): void {
            $table->foreignId('workspace_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->index(['workspace_id', 'created_at']);
        });

        Schema::table('client_activities', function (Blueprint $table): void {
            $table->foreignId('workspace_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->index(['workspace_id', 'created_at']);
        });

        Schema::table('client_attachments', function (Blueprint $table): void {
            $table->foreignId('workspace_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->index(['workspace_id', 'created_at']);
        });

        Schema::table('tags', function (Blueprint $table): void {
            $table->foreignId('workspace_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->index(['workspace_id', 'name']);
        });

        Schema::table('saved_client_views', function (Blueprint $table): void {
            $table->foreignId('workspace_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->index(['workspace_id', 'created_at']);
        });

        $workspaceByUserId = [];
        $users = DB::table('users')->select(['id', 'name', 'email'])->orderBy('id')->get();
        $timestamp = now();

        foreach ($users as $user) {
            $baseName = trim((string) $user->name) !== '' ? "{$user->name}'s Workspace" : "Workspace {$user->id}";
            $slug = Str::slug($baseName).'-'.$user->id;

            $workspaceId = DB::table('workspaces')->insertGetId([
                'owner_user_id' => $user->id,
                'name' => $baseName,
                'slug' => $slug,
                'is_personal' => true,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            DB::table('workspace_user')->insert([
                'workspace_id' => $workspaceId,
                'user_id' => $user->id,
                'role' => WorkspaceMemberRole::Owner->value,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            DB::table('users')
                ->where('id', $user->id)
                ->update(['current_workspace_id' => $workspaceId]);

            $workspaceByUserId[$user->id] = $workspaceId;
        }

        foreach ($workspaceByUserId as $userId => $workspaceId) {
            DB::table('clients')
                ->where('user_id', $userId)
                ->update(['workspace_id' => $workspaceId]);

            DB::table('tags')
                ->where('user_id', $userId)
                ->update(['workspace_id' => $workspaceId]);

            DB::table('saved_client_views')
                ->where('user_id', $userId)
                ->update(['workspace_id' => $workspaceId]);
        }

        DB::table('client_notes')
            ->join('clients', 'clients.id', '=', 'client_notes.client_id')
            ->select(['client_notes.id', 'clients.workspace_id'])
            ->orderBy('client_notes.id')
            ->get()
            ->each(function (object $note): void {
                DB::table('client_notes')
                    ->where('id', $note->id)
                    ->update(['workspace_id' => $note->workspace_id]);
            });

        DB::table('client_activities')
            ->leftJoin('clients', 'clients.id', '=', 'client_activities.client_id')
            ->select(['client_activities.id', 'clients.workspace_id'])
            ->orderBy('client_activities.id')
            ->get()
            ->each(function (object $activity): void {
                DB::table('client_activities')
                    ->where('id', $activity->id)
                    ->update(['workspace_id' => $activity->workspace_id]);
            });

        foreach ($workspaceByUserId as $userId => $workspaceId) {
            DB::table('client_activities')
                ->where('user_id', $userId)
                ->whereNull('workspace_id')
                ->update(['workspace_id' => $workspaceId]);

            DB::table('client_attachments')
                ->where('user_id', $userId)
                ->update(['workspace_id' => $workspaceId]);
        }
    }

    public function down(): void
    {
        Schema::table('saved_client_views', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('workspace_id');
        });

        Schema::table('tags', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('workspace_id');
        });

        Schema::table('client_attachments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('workspace_id');
        });

        Schema::table('client_activities', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('workspace_id');
        });

        Schema::table('client_notes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('workspace_id');
        });

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('workspace_id');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('current_workspace_id');
        });

        Schema::dropIfExists('workspace_user');
        Schema::dropIfExists('workspaces');
    }
};
