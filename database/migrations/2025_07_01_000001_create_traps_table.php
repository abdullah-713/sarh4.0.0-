<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('traps');

        Schema::create('traps', function (Blueprint $table) {
            $table->id();
            $table->string('trap_code', 50)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('trigger_type', ['button_click', 'page_visit', 'form_submit', 'data_export'])->default('button_click');
            $table->float('risk_weight')->default(1.0);
            $table->boolean('is_active')->default(true);
            $table->json('target_levels')->nullable();   // مستويات الأمان المستهدفة
            $table->json('fake_response')->nullable();    // الاستجابة الوهمية
            $table->string('placement', 50)->default('sidebar'); // sidebar, dashboard, settings, toolbar
            $table->string('css_class')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('trigger_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traps');
    }
};
