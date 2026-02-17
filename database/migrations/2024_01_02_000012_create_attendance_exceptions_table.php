<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SarhIndex v1.9.0 — Module 7: Attendance Exception Engine
 *
 * جدول استثناءات الحضور - يسمح لموظفين محددين بتسجيل الحضور
 * خارج ساعات العمل الرسمية بدون تسجيلهم كمتأخرين.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('exception_type', [
                'flexible_hours',    // ساعات مرنة
                'remote_work',       // عمل عن بعد
                'vip_bypass',        // تجاوز VIP
                'medical',           // طبي
                'custom',            // مخصص
            ])->default('flexible_hours');
            $table->time('custom_shift_start')->nullable()->comment('وقت بداية الدوام المخصص');
            $table->time('custom_shift_end')->nullable()->comment('وقت نهاية الدوام المخصص');
            $table->unsignedSmallInteger('custom_grace_minutes')->nullable()->comment('فترة سماح مخصصة');
            $table->boolean('bypass_geofence')->default(false)->comment('تجاوز السياج الجغرافي');
            $table->boolean('bypass_late_flag')->default(true)->comment('لا يُحسب كتأخير');
            $table->date('start_date');
            $table->date('end_date')->nullable()->comment('null = دائم');
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_exceptions');
    }
};
