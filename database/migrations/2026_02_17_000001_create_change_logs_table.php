<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('action_number');
            $table->dateTime('timestamp');
            $table->string('file_path', 500);
            $table->string('change_type', 20); // add, modify, delete
            $table->text('description')->nullable();
            $table->string('commit_hash', 64)->nullable();
            $table->string('file_hash', 64)->nullable(); // SHA-256 للتحقق من التكرار
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();

            $table->index('file_path');
            $table->index('change_type');
            $table->index('timestamp');
            $table->index('action_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_logs');
    }
};
