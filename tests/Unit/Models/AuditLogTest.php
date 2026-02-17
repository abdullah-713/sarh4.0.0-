<?php

namespace Tests\Unit\Models;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_creates_audit_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $branch = Branch::factory()->create();

        $log = AuditLog::record('created', $branch, null, ['name_en' => 'Test'], 'Created branch');

        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'created',
            'auditable_type' => Branch::class,
            'auditable_id'   => $branch->id,
            'description'    => 'Created branch',
        ]);
        $this->assertEquals($user->id, $log->user_id);
    }

    public function test_record_without_model(): void
    {
        $log = AuditLog::record('system_maintenance', null, null, null, 'Cache cleared');
        $this->assertNull($log->auditable_type);
        $this->assertNull($log->auditable_id);
    }

    public function test_scope_for_model(): void
    {
        $branch = Branch::factory()->create();
        AuditLog::record('created', $branch);
        AuditLog::record('updated', $branch);
        AuditLog::record('deleted', Branch::factory()->create());

        $logs = AuditLog::forModel(Branch::class, $branch->id)->count();
        $this->assertEquals(2, $logs);
    }

    public function test_scope_by_action(): void
    {
        AuditLog::record('created', Branch::factory()->create());
        AuditLog::record('deleted', Branch::factory()->create());

        $this->assertEquals(1, AuditLog::byAction('created')->count());
    }

    public function test_morph_to_auditable(): void
    {
        $branch = Branch::factory()->create();
        $log = AuditLog::record('created', $branch);
        $this->assertInstanceOf(Branch::class, $log->auditable);
    }

    public function test_casts_old_new_values_as_array(): void
    {
        $log = AuditLog::record('updated', Branch::factory()->create(), ['old' => 1], ['new' => 2]);
        $log->refresh();
        $this->assertIsArray($log->old_values);
        $this->assertIsArray($log->new_values);
    }
}
