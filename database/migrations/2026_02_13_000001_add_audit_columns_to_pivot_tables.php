<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SarhIndex v3.4 — إضافة أعمدة التدقيق للجداول المحوّلة إلى كيانات مستقلة.
 *
 * user_shifts: assigned_by, reason, approved_at, approved_by
 * user_badges: awarded_by
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_shifts', function (Blueprint $table) {
            $table->foreignId('assigned_by')->nullable()->after('shift_id')
                  ->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable()->after('is_current');
            $table->timestamp('approved_at')->nullable()->after('reason');
            $table->foreignId('approved_by')->nullable()->after('approved_at')
                  ->constrained('users')->nullOnDelete();
        });

        Schema::table('user_badges', function (Blueprint $table) {
            $table->foreignId('awarded_by')->nullable()->after('awarded_reason')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_shifts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_by');
            $table->dropColumn('reason');
            $table->dropColumn('approved_at');
            $table->dropConstrainedForeignId('approved_by');
        });

        Schema::table('user_badges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('awarded_by');
        });
    }
};
