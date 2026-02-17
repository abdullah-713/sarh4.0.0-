<?php

namespace Tests\Feature\EmployeePortal;

use App\Models\User;
use App\Models\Branch;
use App\Models\AttendanceLog;
use App\Models\LeaveRequest;
use App\Filament\App\Resources\AttendanceResource;
use App\Filament\App\Resources\LeaveResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * اختبارات موارد الموظف — الحضور والإجازات
 */
class EmployeeResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected User $employee;
    protected User $otherEmployee;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::factory()->create();

        $this->employee = User::factory()->create([
            'branch_id' => $this->branch->id,
            'security_level' => 2,
            'status' => 'active',
        ]);

        $this->otherEmployee = User::factory()->create([
            'branch_id' => $this->branch->id,
            'security_level' => 2,
            'status' => 'active',
        ]);
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-001: AttendanceResource scopes to current user
    // ═══════════════════════════════════════════════════
    public function test_attendance_resource_scopes_to_current_user(): void
    {
        // Create attendance logs for both employees
        $myLog = AttendanceLog::factory()->withCheckOut()->create([
            'user_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
        ]);

        $otherLog = AttendanceLog::factory()->withCheckOut()->create([
            'user_id' => $this->otherEmployee->id,
            'branch_id' => $this->branch->id,
        ]);

        // Act as the first employee
        $this->actingAs($this->employee);

        // Get the query from AttendanceResource
        $query = AttendanceResource::getEloquentQuery();
        $results = $query->get();

        // Should only see own attendance
        $this->assertTrue($results->contains('id', $myLog->id));
        $this->assertFalse($results->contains('id', $otherLog->id));
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-002: LeaveResource scopes to current user
    // ═══════════════════════════════════════════════════
    public function test_leave_resource_scopes_to_current_user(): void
    {
        // Create leave requests for both employees
        $myLeave = LeaveRequest::factory()->create([
            'user_id' => $this->employee->id,
        ]);

        $otherLeave = LeaveRequest::factory()->create([
            'user_id' => $this->otherEmployee->id,
        ]);

        // Act as the first employee
        $this->actingAs($this->employee);

        // Get the query from LeaveResource
        $query = LeaveResource::getEloquentQuery();
        $results = $query->get();

        // Should only see own leave requests
        $this->assertTrue($results->contains('id', $myLeave->id));
        $this->assertFalse($results->contains('id', $otherLeave->id));
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-003: Employee cannot view other's attendance via URL
    // ═══════════════════════════════════════════════════
    public function test_employee_cannot_view_others_attendance_via_url(): void
    {
        $otherLog = AttendanceLog::factory()->withCheckOut()->create([
            'user_id' => $this->otherEmployee->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->get("/app/my-attendance/{$otherLog->id}");

        // Should be 404 or forbidden since the record doesn't appear in scoped query
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-004: Employee can view own attendance
    // ═══════════════════════════════════════════════════
    public function test_employee_can_view_own_attendance(): void
    {
        $myLog = AttendanceLog::factory()->withCheckOut()->create([
            'user_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->get('/app/my-attendance');

        $response->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-005: Employee can access create attendance page
    // ═══════════════════════════════════════════════════
    public function test_employee_can_access_create_attendance_page(): void
    {
        $response = $this->actingAs($this->employee)
            ->get('/app/my-attendance/create');

        $response->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-006: Employee can view own leaves
    // ═══════════════════════════════════════════════════
    public function test_employee_can_view_own_leaves(): void
    {
        LeaveRequest::factory()->create([
            'user_id' => $this->employee->id,
        ]);

        $response = $this->actingAs($this->employee)
            ->get('/app/my-leaves');

        $response->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-007: Employee can access create leave page
    // ═══════════════════════════════════════════════════
    public function test_employee_can_access_create_leave_page(): void
    {
        $response = $this->actingAs($this->employee)
            ->get('/app/my-leaves/create');

        $response->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-008: AttendanceResource has correct model
    // ═══════════════════════════════════════════════════
    public function test_attendance_resource_has_correct_model(): void
    {
        $this->assertEquals(AttendanceLog::class, AttendanceResource::getModel());
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-009: LeaveResource has correct model
    // ═══════════════════════════════════════════════════
    public function test_leave_resource_has_correct_model(): void
    {
        $this->assertEquals(LeaveRequest::class, LeaveResource::getModel());
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-010: AttendanceResource slug is my-attendance
    // ═══════════════════════════════════════════════════
    public function test_attendance_resource_slug_is_correct(): void
    {
        $this->assertEquals('my-attendance', AttendanceResource::getSlug());
    }

    // ═══════════════════════════════════════════════════
    // TC-ER-011: LeaveResource slug is my-leaves
    // ═══════════════════════════════════════════════════
    public function test_leave_resource_slug_is_correct(): void
    {
        $this->assertEquals('my-leaves', LeaveResource::getSlug());
    }
}
