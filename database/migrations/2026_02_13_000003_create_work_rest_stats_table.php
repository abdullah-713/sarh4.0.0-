<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_rest_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('stat_date');

            // Aggregates
            $table->integer('total_readings')->default(0);
            $table->integer('work_readings')->default(0);
            $table->integer('rest_readings')->default(0);
            $table->integer('anomaly_readings')->default(0);

            // Calculated metrics
            $table->decimal('work_minutes', 8, 2)->default(0);
            $table->decimal('rest_minutes', 8, 2)->default(0);
            $table->decimal('productivity_ratio', 5, 2)->default(0)->comment('percentage');

            // Financial impact
            $table->decimal('expected_work_minutes', 8, 2)->default(0);
            $table->decimal('vpm_leak', 8, 2)->default(0)->comment('SAR lost');
            $table->decimal('wasted_salary', 8, 2)->default(0);

            // Flags
            $table->enum('rating', ['golden', 'normal', 'leaking', 'critical'])->default('normal');
            $table->boolean('needs_review')->default(false);

            $table->timestamps();
            $table->unique(['user_id', 'stat_date']);
            $table->index(['stat_date', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_rest_stats');
    }
};
