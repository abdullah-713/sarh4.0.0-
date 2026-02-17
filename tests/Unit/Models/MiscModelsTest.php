<?php

namespace Tests\Unit\Models;

use App\Models\Branch;
use App\Models\Department;
use App\Models\EmployeeDocument;
use App\Models\EmployeePattern;
use App\Models\EmployeeReminder;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiscModelsTest extends TestCase
{
    use RefreshDatabase;

    // ── Department ──
    public function test_department_belongs_to_branch(): void
    {
        $dept = Department::factory()->create();
        $this->assertInstanceOf(Branch::class, $dept->branch);
    }

    public function test_department_has_many_users(): void
    {
        $dept = Department::factory()->create();
        User::factory()->create(['department_id' => $dept->id, 'branch_id' => $dept->branch_id]);
        $this->assertEquals(1, $dept->users()->count());
    }

    public function test_department_scope_active(): void
    {
        Department::factory()->create(['is_active' => true]);
        Department::factory()->create(['is_active' => false]);
        $this->assertEquals(1, Department::active()->count());
    }

    public function test_department_soft_deletes(): void
    {
        $dept = Department::factory()->create();
        $dept->delete();
        $this->assertSoftDeleted('departments', ['id' => $dept->id]);
    }

    public function test_department_name_accessor(): void
    {
        app()->setLocale('ar');
        $dept = Department::factory()->create(['name_ar' => 'تقنية', 'name_en' => 'IT']);
        $this->assertEquals('تقنية', $dept->name);
    }

    // ── Branch ──
    public function test_branch_has_many_users(): void
    {
        $branch = Branch::factory()->create();
        User::factory()->create(['branch_id' => $branch->id]);
        $this->assertEquals(1, $branch->users()->count());
    }

    public function test_branch_scope_active(): void
    {
        Branch::factory()->create(['is_active' => true]);
        Branch::factory()->create(['is_active' => false]);
        $this->assertEquals(1, Branch::active()->count());
    }

    public function test_branch_distance_to(): void
    {
        $branch = Branch::factory()->withCoordinates(24.7136, 46.6753)->create();
        $dist = $branch->distanceTo(24.7136, 46.6753);
        $this->assertEquals(0, $dist);
    }

    public function test_branch_is_within_geofence(): void
    {
        $branch = Branch::factory()->withCoordinates(24.7136, 46.6753)->create(['geofence_radius' => 17]);
        $this->assertTrue($branch->isWithinGeofence(24.7136, 46.6753));
    }

    public function test_branch_outside_geofence(): void
    {
        $branch = Branch::factory()->withCoordinates(24.7136, 46.6753)->create(['geofence_radius' => 17]);
        $this->assertFalse($branch->isWithinGeofence(24.7140, 46.6760));
    }

    public function test_branch_soft_deletes(): void
    {
        $branch = Branch::factory()->create();
        $branch->delete();
        $this->assertSoftDeleted('branches', ['id' => $branch->id]);
    }

    // ── EmployeeDocument ──
    public function test_employee_document_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $doc = EmployeeDocument::create([
            'user_id' => $user->id,
            'document_type' => 'id_card',
            'document_number' => 'DOC-001',
            'file_path' => 'documents/test.pdf',
            'file_type' => 'pdf',
        ]);
        $this->assertInstanceOf(User::class, $doc->user);
    }

    public function test_employee_document_is_expired(): void
    {
        $user = User::factory()->create();
        $doc = EmployeeDocument::create([
            'user_id' => $user->id,
            'document_type' => 'id_card',
            'document_number' => 'DOC-002',
            'file_path' => 'test.pdf',
            'file_type' => 'pdf',
            'expiry_date' => now()->subDay(),
        ]);
        $this->assertTrue($doc->is_expired);
    }

    public function test_employee_document_is_expiring(): void
    {
        $user = User::factory()->create();
        $doc = EmployeeDocument::create([
            'user_id' => $user->id,
            'document_type' => 'passport',
            'document_number' => 'DOC-003',
            'file_path' => 'test.pdf',
            'file_type' => 'pdf',
            'expiry_date' => now()->addDays(15),
        ]);
        $this->assertTrue($doc->is_expiring);
    }

    public function test_employee_document_status_color(): void
    {
        $user = User::factory()->create();
        $doc = EmployeeDocument::create([
            'user_id' => $user->id,
            'document_type' => 'id',
            'document_number' => 'D1',
            'file_path' => 't.pdf',
            'file_type' => 'pdf',
            'expiry_date' => now()->subDay(),
        ]);
        $this->assertEquals('danger', $doc->status_color);
    }

    public function test_employee_document_scope_expiring_soon(): void
    {
        $user = User::factory()->create();
        EmployeeDocument::create([
            'user_id' => $user->id, 'document_type' => 'a', 'document_number' => 'A1',
            'file_path' => 'a.pdf', 'file_type' => 'pdf', 'expiry_date' => now()->addDays(10),
        ]);
        EmployeeDocument::create([
            'user_id' => $user->id, 'document_type' => 'b', 'document_number' => 'B1',
            'file_path' => 'b.pdf', 'file_type' => 'pdf', 'expiry_date' => now()->addDays(100),
        ]);
        $this->assertEquals(1, EmployeeDocument::expiringSoon(90)->count());
    }

    // ── LeaveRequest ──
    public function test_leave_request_belongs_to_user(): void
    {
        $leave = LeaveRequest::factory()->create();
        $this->assertInstanceOf(User::class, $leave->user);
    }

    public function test_leave_request_scope_pending(): void
    {
        LeaveRequest::factory()->create(['status' => 'pending']);
        LeaveRequest::factory()->approved()->create();
        $this->assertEquals(1, LeaveRequest::pending()->count());
    }

    public function test_leave_request_scope_approved(): void
    {
        LeaveRequest::factory()->approved()->create();
        LeaveRequest::factory()->create(['status' => 'pending']);
        $this->assertEquals(1, LeaveRequest::approved()->count());
    }

    public function test_leave_request_soft_deletes(): void
    {
        $leave = LeaveRequest::factory()->create();
        $leave->delete();
        $this->assertSoftDeleted('leave_requests', ['id' => $leave->id]);
    }

    // ── EmployeePattern ──
    public function test_employee_pattern_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();
        $pattern = EmployeePattern::create([
            'user_id'          => $user->id,
            'branch_id'        => $branch->id,
            'pattern_type'     => 'frequent_late',
            'description_ar'   => 'تأخر متكرر',
            'frequency_score'  => 0.8,
            'financial_impact' => 500,
            'risk_level'       => 'high',
            'detected_at'      => now(),
            'is_active'        => true,
        ]);
        $this->assertInstanceOf(User::class, $pattern->user);
    }

    public function test_employee_pattern_scope_high_risk(): void
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();
        EmployeePattern::create([
            'user_id' => $user->id, 'branch_id' => $branch->id,
            'pattern_type' => 'frequent_late', 'description_ar' => 'تأخر', 'risk_level' => 'high', 'is_active' => true,
            'detected_at' => now(),
        ]);
        EmployeePattern::create([
            'user_id' => $user->id, 'branch_id' => $branch->id,
            'pattern_type' => 'improving', 'description_ar' => 'تحسن', 'risk_level' => 'low', 'is_active' => true,
            'detected_at' => now(),
        ]);
        $this->assertEquals(1, EmployeePattern::highRisk()->count());
    }

    public function test_employee_pattern_types(): void
    {
        $types = EmployeePattern::patternTypes();
        $this->assertArrayHasKey('frequent_late', $types);
        $this->assertArrayHasKey('burnout_risk', $types);
    }

    public function test_employee_pattern_risk_color(): void
    {
        $user = User::factory()->create();
        $branch = Branch::factory()->create();
        $pattern = EmployeePattern::create([
            'user_id' => $user->id, 'branch_id' => $branch->id,
            'pattern_type' => 'frequent_late', 'description_ar' => 'حرج', 'risk_level' => 'critical',
            'is_active' => true, 'detected_at' => now(),
        ]);
        $this->assertEquals('danger', $pattern->getRiskColor());
    }
}
