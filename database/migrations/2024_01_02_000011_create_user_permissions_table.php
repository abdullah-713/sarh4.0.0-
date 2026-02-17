<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SarhIndex v1.9.0 — Module 2: Granular RBAC
 *
 * جدول تجاوز الصلاحيات على مستوى المستخدم الفردي.
 * يسمح بمنح صلاحية محددة لمستخدم بغض النظر عن دوره.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- User ↔ Permission Direct Override ---
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->enum('type', ['grant', 'revoke'])->default('grant')
                  ->comment('grant = إضافة صلاحية, revoke = سحب صلاحية');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable()->comment('انتهاء صلاحية التجاوز');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'permission_id']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
