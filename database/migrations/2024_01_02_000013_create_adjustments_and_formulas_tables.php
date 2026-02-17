<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SarhIndex v1.9.0 — Module 8: Manual Adjustments & Dynamic Reporting
 *
 * جدول تعديلات النقاط/الدرجات اليدوية + جدول صيغ التقارير الديناميكية.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Branch Score Adjustments ---
        Schema::create('score_adjustments', function (Blueprint $table) {
            $table->id();
            $table->enum('scope', ['branch', 'user', 'department'])->default('branch');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->integer('points_delta')->comment('موجب أو سالب');
            $table->decimal('value_delta', 12, 2)->default(0)->comment('تعديل القيمة المالية');
            $table->string('category')->default('manual')->comment('manual, bonus, penalty, correction');
            $table->text('reason');
            $table->foreignId('adjusted_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['scope', 'branch_id']);
            $table->index(['scope', 'user_id']);
        });

        // --- Dynamic Report Formulas ---
        Schema::create('report_formulas', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('slug')->unique();
            $table->text('formula')->comment('الصيغة الحسابية e.g. (attendance * 0.4) + (task_completion * 0.6)');
            $table->json('variables')->comment('المتغيرات المتاحة وتعريفاتها');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_formulas');
        Schema::dropIfExists('score_adjustments');
    }
};
