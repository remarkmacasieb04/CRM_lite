<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 20)->default(UserRole::User->value)->after('password');
            $table->boolean('receives_follow_up_reminders')->default(true)->after('role');
            $table->date('last_follow_up_digest_sent_at')->nullable()->after('receives_follow_up_reminders');

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['role']);
            $table->dropColumn([
                'role',
                'receives_follow_up_reminders',
                'last_follow_up_digest_sent_at',
            ]);
        });
    }
};
