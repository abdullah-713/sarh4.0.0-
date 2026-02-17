<?php

namespace Tests\Unit\Models;

use App\Models\ChangeLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_next_action_number_starts_at_1(): void
    {
        $this->assertEquals(1, ChangeLog::nextActionNumber());
    }

    public function test_next_action_number_increments(): void
    {
        ChangeLog::create([
            'action_number' => 1,
            'timestamp'     => now(),
            'file_path'     => 'app/Models/User.php',
            'change_type'   => 'modified',
            'description'   => 'Test change',
        ]);
        $this->assertEquals(2, ChangeLog::nextActionNumber());
    }

    public function test_is_duplicate_returns_false_without_hash(): void
    {
        $this->assertFalse(ChangeLog::isDuplicate('app/test.php', 'modified', null));
    }

    public function test_is_duplicate_detects_recent_same_hash(): void
    {
        ChangeLog::create([
            'action_number' => 1,
            'timestamp'     => now(),
            'file_path'     => 'app/test.php',
            'change_type'   => 'modified',
            'description'   => 'Test',
            'file_hash'     => 'abc123',
        ]);
        $this->assertTrue(ChangeLog::isDuplicate('app/test.php', 'modified', 'abc123'));
    }

    public function test_is_duplicate_returns_false_for_different_hash(): void
    {
        ChangeLog::create([
            'action_number' => 1,
            'timestamp'     => now(),
            'file_path'     => 'app/test.php',
            'change_type'   => 'modified',
            'description'   => 'Test',
            'file_hash'     => 'abc123',
        ]);
        $this->assertFalse(ChangeLog::isDuplicate('app/test.php', 'modified', 'xyz789'));
    }
}
