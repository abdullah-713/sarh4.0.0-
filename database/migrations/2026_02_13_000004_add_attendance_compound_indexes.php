<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * v4.3: Additional compound indexes for attendance_logs
     * to optimize date-based branch/status/user queries.
     */
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->index(['user_id', 'attendance_date'], 'idx_attendance_user_date');
            $table->index(['branch_id', 'attendance_date'], 'idx_attendance_branch_date');
            $table->index(['status', 'attendance_date'], 'idx_attendance_status_date');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropIndex('idx_attendance_user_date');
            $table->dropIndex('idx_attendance_branch_date');
            $table->dropIndex('idx_attendance_status_date');
        });
    }
};
