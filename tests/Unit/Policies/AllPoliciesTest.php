<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\BranchPolicy;
use App\Policies\CircularPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployeeDocumentPolicy;
use App\Policies\HolidayPolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\PayrollPolicy;
use App\Policies\PerformanceAlertPolicy;
use App\Policies\ShiftPolicy;
use Tests\TestCase;

class AllPoliciesTest extends TestCase
{
    private function makeUser(int $level = 1, bool $super = false): User
    {
        $user = new User([
            'employee_id' => 'TEST-' . rand(1000, 9999),
            'name_ar' => 'تست', 'name_en' => 'Test',
            'email' => fake()->email(),
            'basic_salary' => 5000,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
        ]);
        $user->id = rand(1, 9999);
        $user->security_level = $level;
        $user->is_super_admin = $super;
        return $user;
    }

    // ── BranchPolicy ──

    public function test_branch_view_any_all_users(): void
    {
        $policy = new BranchPolicy();
        $this->assertTrue($policy->viewAny($this->makeUser(1)));
        $this->assertTrue($policy->viewAny($this->makeUser(10)));
    }

    public function test_branch_create_level_10_plus(): void
    {
        $policy = new BranchPolicy();
        $this->assertTrue($policy->create($this->makeUser(10)));
        $this->assertFalse($policy->create($this->makeUser(9)));
    }

    public function test_branch_delete_level_10_only(): void
    {
        $policy = new BranchPolicy();
        $this->assertTrue($policy->delete($this->makeUser(10), new \App\Models\Branch()));
        $this->assertFalse($policy->delete($this->makeUser(9), new \App\Models\Branch()));
    }

    public function test_branch_super_admin_bypass(): void
    {
        $policy = new BranchPolicy();
        $user = $this->makeUser(1, true);
        $this->assertTrue($policy->viewAny($user));
        $this->assertTrue($policy->create($user));
        $this->assertTrue($policy->delete($user, new \App\Models\Branch()));
    }

    // ── DepartmentPolicy ──

    public function test_department_view_any_all_users(): void
    {
        $policy = new DepartmentPolicy();
        $this->assertTrue($policy->viewAny($this->makeUser(1)));
    }

    public function test_department_create_level_7_plus(): void
    {
        $policy = new DepartmentPolicy();
        $this->assertTrue($policy->create($this->makeUser(7)));
        $this->assertFalse($policy->create($this->makeUser(6)));
    }

    public function test_department_delete_level_10(): void
    {
        $policy = new DepartmentPolicy();
        $this->assertTrue($policy->delete($this->makeUser(10), new \App\Models\Department()));
        $this->assertFalse($policy->delete($this->makeUser(9), new \App\Models\Department()));
    }

    // ── CircularPolicy ──

    public function test_circular_view_any_all_users(): void
    {
        $policy = new CircularPolicy();
        $this->assertTrue($policy->viewAny($this->makeUser(1)));
    }

    public function test_circular_create_level_6_plus(): void
    {
        $policy = new CircularPolicy();
        $this->assertTrue($policy->create($this->makeUser(6)));
        $this->assertFalse($policy->create($this->makeUser(5)));
    }

    public function test_circular_delete_level_10(): void
    {
        $policy = new CircularPolicy();
        $this->assertTrue($policy->delete($this->makeUser(10), new \App\Models\Circular()));
        $this->assertFalse($policy->delete($this->makeUser(9), new \App\Models\Circular()));
    }

    // ── HolidayPolicy ──

    public function test_holiday_view_any_all_users(): void
    {
        $policy = new HolidayPolicy();
        $this->assertTrue($policy->viewAny($this->makeUser(1)));
    }

    public function test_holiday_create_level_6_plus(): void
    {
        $policy = new HolidayPolicy();
        $this->assertTrue($policy->create($this->makeUser(6)));
        $this->assertFalse($policy->create($this->makeUser(5)));
    }

    public function test_holiday_delete_level_10(): void
    {
        $policy = new HolidayPolicy();
        $this->assertTrue($policy->delete($this->makeUser(10), new \App\Models\Holiday()));
        $this->assertFalse($policy->delete($this->makeUser(9), new \App\Models\Holiday()));
    }

    // ── ShiftPolicy ──

    public function test_shift_view_any_all_users(): void
    {
        $policy = new ShiftPolicy();
        $this->assertTrue($policy->viewAny($this->makeUser(1)));
    }

    public function test_shift_create_level_7(): void
    {
        $policy = new ShiftPolicy();
        $this->assertTrue($policy->create($this->makeUser(7)));
        $this->assertFalse($policy->create($this->makeUser(6)));
    }

    public function test_shift_delete_level_10(): void
    {
        $policy = new ShiftPolicy();
        $this->assertTrue($policy->delete($this->makeUser(10), new \App\Models\Shift()));
        $this->assertFalse($policy->delete($this->makeUser(9), new \App\Models\Shift()));
    }

    // ── PayrollPolicy ──

    public function test_payroll_view_any_level_7(): void
    {
        $policy = new PayrollPolicy();
        $this->assertTrue($policy->viewAny($this->makeUser(7)));
        $this->assertFalse($policy->viewAny($this->makeUser(6)));
    }

    public function test_payroll_create_level_7(): void
    {
        $policy = new PayrollPolicy();
        $this->assertTrue($policy->create($this->makeUser(7)));
        $this->assertFalse($policy->create($this->makeUser(6)));
    }

    public function test_payroll_delete_level_10(): void
    {
        $policy = new PayrollPolicy();
        $this->assertTrue($policy->delete($this->makeUser(10), new \App\Models\Payroll()));
        $this->assertFalse($policy->delete($this->makeUser(9), new \App\Models\Payroll()));
    }

    // ── LeaveRequestPolicy ──

    public function test_leave_request_view_any_all_users(): void
    {
        $policy = new LeaveRequestPolicy();
        $this->assertTrue($policy->viewAny($this->makeUser(1)));
    }

    public function test_leave_request_create_all_users(): void
    {
        $policy = new LeaveRequestPolicy();
        $this->assertTrue($policy->create($this->makeUser(1)));
    }

    public function test_leave_request_delete_self_if_pending(): void
    {
        $policy = new LeaveRequestPolicy();
        $lr = new \App\Models\LeaveRequest();
        $lr->status = 'pending';

        $user = $this->makeUser(1);
        $lr->user_id = $user->id;
        $this->assertTrue($policy->delete($user, $lr));

        $lr->status = 'approved';
        $this->assertFalse($policy->delete($user, $lr));
    }

    // ── EmployeeDocumentPolicy ──

    public function test_employee_doc_view_any_all_users(): void
    {
        $policy = new EmployeeDocumentPolicy();
        $this->assertTrue($policy->viewAny($this->makeUser(1)));
    }

    public function test_employee_doc_create_level_6(): void
    {
        $policy = new EmployeeDocumentPolicy();
        $this->assertTrue($policy->create($this->makeUser(6)));
        $this->assertFalse($policy->create($this->makeUser(5)));
    }

    public function test_employee_doc_delete_level_10(): void
    {
        $policy = new EmployeeDocumentPolicy();
        $this->assertTrue($policy->delete($this->makeUser(10), new \App\Models\EmployeeDocument()));
        $this->assertFalse($policy->delete($this->makeUser(9), new \App\Models\EmployeeDocument()));
    }

    // ── PerformanceAlertPolicy ──

    public function test_performance_alert_view_any_level_5(): void
    {
        $policy = new PerformanceAlertPolicy();
        $this->assertTrue($policy->viewAny($this->makeUser(5)));
        $this->assertFalse($policy->viewAny($this->makeUser(4)));
    }

    public function test_performance_alert_create_level_5(): void
    {
        $policy = new PerformanceAlertPolicy();
        $this->assertTrue($policy->create($this->makeUser(5)));
        $this->assertFalse($policy->create($this->makeUser(4)));
    }

    public function test_performance_alert_delete_level_10(): void
    {
        $policy = new PerformanceAlertPolicy();
        $this->assertTrue($policy->delete($this->makeUser(10), new \App\Models\PerformanceAlert()));
        $this->assertFalse($policy->delete($this->makeUser(9), new \App\Models\PerformanceAlert()));
    }
}
