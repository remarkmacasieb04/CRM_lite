<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('status', 32);
            $table->decimal('budget', 12, 2)->nullable();
            $table->string('source', 120)->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('follow_up_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'follow_up_at']);
            $table->index(['user_id', 'archived_at']);
            $table->index(['user_id', 'updated_at']);
            $table->index(['user_id', 'email']);
            $table->index(['user_id', 'name']);
            $table->index(['user_id', 'company']);
            $table->index(['user_id', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
