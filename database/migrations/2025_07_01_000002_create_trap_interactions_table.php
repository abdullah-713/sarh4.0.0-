<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('trap_interactions');

        Schema::create('trap_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trap_id')->constrained('traps')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->float('risk_score')->default(0);
            $table->enum('action_taken', ['logged', 'warned', 'escalated'])->default('logged');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer_url')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedInteger('interaction_count')->default(1);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['trap_id', 'created_at']);
            $table->index('risk_score');
            $table->index('action_taken');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trap_interactions');
    }
};
