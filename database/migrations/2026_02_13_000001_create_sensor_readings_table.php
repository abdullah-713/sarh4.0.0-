<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_log_id')->constrained()->onDelete('cascade');

            // Edge-processed features
            $table->decimal('avg_accel_x', 8, 4)->nullable();
            $table->decimal('avg_accel_y', 8, 4)->nullable();
            $table->decimal('avg_accel_z', 8, 4)->nullable();
            $table->decimal('variance_motion', 10, 4)->nullable();
            $table->decimal('peak_frequency', 8, 2)->nullable()->comment('Hz');
            $table->decimal('db_level', 5, 2)->nullable()->comment('Decibels');

            // ML outputs
            $table->decimal('work_probability', 3, 2)->default(0)->comment('0.00 to 1.00');
            $table->enum('motion_signature', [
                'mechanical', 'walking', 'stationary', 'suspicious', 'unknown',
            ])->default('unknown');

            // Anomaly detection
            $table->boolean('is_anomaly')->default(false);
            $table->string('anomaly_reason')->nullable();

            // Metadata
            $table->integer('sampling_window')->default(30)->comment('seconds');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for analytics queries
            $table->index(['user_id', 'created_at']);
            $table->index('attendance_log_id');
            $table->index(['is_anomaly', 'created_at']);
            $table->index('work_probability');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
