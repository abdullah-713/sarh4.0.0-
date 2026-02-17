<?php

namespace Tests\Unit\Models;

use App\Models\AttendanceException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceExceptionTest extends TestCase
{
    use RefreshDatabase;

    private function createException(array $attrs = []): AttendanceException
    {
        return AttendanceException::create(array_merge([
            'user_id'              => User::factory()->create()->id,
            'exception_type'       => 'custom_shift',
            'custom_shift_start'   => '09:00',
            'custom_shift_end'     => '17:00',
            'custom_grace_minutes' => 15,
            'bypass_geofence'      => false,
            'bypass_late_flag'     => false,
            'start_date'           => now()->subDay()->toDateString(),
            'end_date'             => now()->addDay()->toDateString(),
            'reason'               => 'Testing',
            'is_active'            => true,
        ], $attrs));
    }

    public function test_belongs_to_user(): void
    {
        $exc = $this->createException();
        $this->assertInstanceOf(User::class, $exc->user);
    }

    public function test_belongs_to_approved_by_user(): void
    {
        $approver = User::factory()->create();
        $exc = $this->createException(['approved_by' => $approver->id]);
        $this->assertEquals($approver->id, $exc->approvedByUser->id);
    }

    public function test_scope_active_today(): void
    {
        $this->createException(['is_active' => true, 'start_date' => now()->subDay(), 'end_date' => now()->addDay()]);
        $this->createException(['is_active' => false]);
        $this->createException(['is_active' => true, 'start_date' => now()->addWeek()]);
        $this->assertEquals(1, AttendanceException::activeToday()->count());
    }

    public function test_scope_for_user(): void
    {
        $user = User::factory()->create();
        $this->createException(['user_id' => $user->id]);
        $this->createException();
        $this->assertEquals(1, AttendanceException::forUser($user->id)->count());
    }

    public function test_get_active_for_user(): void
    {
        $user = User::factory()->create();
        $this->createException(['user_id' => $user->id]);
        $result = AttendanceException::getActiveForUser($user->id);
        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->user_id);
    }

    public function test_is_valid_today(): void
    {
        $exc = $this->createException([
            'is_active'  => true,
            'start_date' => now()->subDay(),
            'end_date'   => now()->addDay(),
        ]);
        $this->assertTrue($exc->isValidToday());
    }

    public function test_is_not_valid_when_inactive(): void
    {
        $exc = $this->createException(['is_active' => false]);
        $this->assertFalse($exc->isValidToday());
    }

    public function test_is_not_valid_when_expired(): void
    {
        $exc = $this->createException([
            'is_active'  => true,
            'start_date' => now()->subWeek(),
            'end_date'   => now()->subDay(),
        ]);
        $this->assertFalse($exc->isValidToday());
    }

    public function test_is_valid_with_null_end_date(): void
    {
        $exc = $this->createException([
            'is_active'  => true,
            'start_date' => now()->subDay(),
            'end_date'   => null,
        ]);
        $this->assertTrue($exc->isValidToday());
    }

    public function test_get_effective_shift_start(): void
    {
        $exc = $this->createException(['custom_shift_start' => '10:00']);
        $this->assertEquals('10:00', $exc->getEffectiveShiftStart());
    }

    public function test_get_effective_grace_period(): void
    {
        $exc = $this->createException(['custom_grace_minutes' => 30]);
        $this->assertEquals(30, $exc->getEffectiveGracePeriod());
    }
}
