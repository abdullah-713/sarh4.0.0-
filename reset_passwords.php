#!/usr/bin/env php
<?php

/**
 * SARH Password Reset Script
 * Sets unified password (123456) for ALL employees
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== SARH Password Reset (Unified: 123456) ===\n\n";

// Get all users
$users = User::all();
$totalUsers = $users->count();

if ($totalUsers === 0) {
    echo "ERROR: No users found in database!\n";
    exit(1);
}

echo "Found {$totalUsers} users. Resetting passwords...\n\n";

$newPassword = Hash::make('123456');
$updated = 0;

foreach ($users as $user) {
    $user->password = $newPassword;
    $user->save();
    
    $statusIcon = $user->status === 'active' ? '✓' : '✗';
    $levelIcon = $user->security_level >= 4 ? '🔑' : '  ';
    
    echo "{$statusIcon} {$levelIcon} [{$user->employee_id}] {$user->name_en} ({$user->email})\n";
    echo "   Security Level: {$user->security_level} | Status: {$user->status}\n";
    
    $updated++;
}

echo "\n=== Summary ===\n";
echo "Total Users: {$totalUsers}\n";
echo "Passwords Updated: {$updated}\n";
echo "\nUnified Password: 123456\n";
echo "\nACCESS REQUIREMENTS:\n";
echo "- Status must be 'active'\n";
echo "- Security Level >= 4 (or is_super_admin = true)\n";
echo "\n=== Done ===\n";
