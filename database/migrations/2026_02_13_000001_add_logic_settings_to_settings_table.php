<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SarhIndex v3.2 — Add logic_settings JSON column to settings table.
 * Stores dynamic business rules: loss_multiplier, geofence_radius, grace_period, overtime_multiplier.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('logic_settings')->nullable()->after('pwa_background_color');
        });

        // Seed default logic settings
        \Illuminate\Support\Facades\DB::table('settings')
            ->where('id', 1)
            ->update([
                'logic_settings' => json_encode([
                    'loss_multiplier'          => 2.0,
                    'default_geofence_radius'  => 100,
                    'default_grace_period'     => 10,
                    'overtime_multiplier'      => 1.5,
                ]),
            ]);
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('logic_settings');
        });
    }
};
