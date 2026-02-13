<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomaly_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sensor_reading_id')->constrained()->onDelete('cascade');
            $table->enum('anomaly_type', [
                'location_mismatch',
                'perfect_signal',
                'no_motion_timeout',
                'frequency_mismatch',
                'replay_attack',
                'impossible_frequency',
            ]);
            $table->decimal('confidence', 3, 2)->default(1.00);
            $table->json('context_data')->nullable();
            $table->boolean('is_reviewed')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['anomaly_type', 'is_reviewed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomaly_logs');
    }
};
