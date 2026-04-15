<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_communications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 30);
            $table->string('direction', 20);
            $table->string('subject')->nullable();
            $table->text('summary');
            $table->timestamp('happened_at');
            $table->timestamps();

            $table->index(['workspace_id', 'client_id', 'happened_at']);
            $table->index(['workspace_id', 'channel', 'happened_at']);
        });

        Schema::create('client_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('title');
            $table->string('document_number', 40);
            $table->string('status', 20)->default('draft');
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_portal_visible')->default(true);
            $table->timestamps();

            $table->unique(['workspace_id', 'document_number']);
            $table->index(['workspace_id', 'client_id', 'type']);
            $table->index(['workspace_id', 'status', 'due_at']);
        });

        Schema::create('client_portal_shares', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 80)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'client_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_portal_shares');
        Schema::dropIfExists('client_documents');
        Schema::dropIfExists('client_communications');
    }
};
