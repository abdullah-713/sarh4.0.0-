<?php

namespace Tests\Unit\Events;

use App\Events\AnomalyDetected;
use App\Events\AttendanceRecorded;
use App\Events\BadgeAwarded;
use App\Events\TrapTriggered;
use App\Listeners\HandleAnomalyDetected;
use App\Listeners\HandleAttendanceRecorded;
use App\Listeners\HandleBadgePoints;
use App\Listeners\HandleTrapTriggered;
use App\Models\AnomalyLog;
use App\Models\AttendanceLog;
use App\Models\AuditLog;
use App\Models\Badge;
use App\Models\Branch;
use App\Models\PerformanceAlert;
use App\Models\Trap;
use App\Models\TrapInteraction;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EventsAndListenersTest extends TestCase
{
    use RefreshDatabase;

    // ── Event Instantiation ──

    public function test_attendance_recorded_event_holds_log(): void
    {
        $log = new AttendanceLog();
        $event = new AttendanceRecorded($log);
        $this->assertSame($log, $event->log);
    }

    public function test_badge_awarded_event_holds_user_badge(): void
    {
        $ub = new UserBadge();
        $event = new BadgeAwarded($ub);
        $this->assertSame($ub, $event->userBadge);
    }

    public function test_trap_triggered_event_holds_data(): void
    {
        $trap = new Trap();
        $user = new User();
        $interaction = new TrapInteraction();
        $event = new TrapTriggered($trap, $user, $interaction);
        $this->assertSame($trap, $event->trap);
        $this->assertSame($user, $event->user);
        $this->assertSame($interaction, $event->interaction);
    }

    // ── HandleAttendanceRecorded Listener ──

    public function test_handle_attendance_recorded_creates_audit_log(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        $log = AttendanceLog::factory()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
        ]);

        $event = new AttendanceRecorded($log);
        $listener = new HandleAttendanceRecorded();
        $listener->handle($event);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'attendance_checkin',
        ]);
    }

    public function test_handle_attendance_recorded_checkout_action(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        $log = AttendanceLog::factory()->withCheckOut()->create([
            'user_id' => $user->id,
            'branch_id' => $branch->id,
        ]);

        $event = new AttendanceRecorded($log);
        $listener = new HandleAttendanceRecorded();
        $listener->handle($event);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'attendance_checkout',
        ]);
    }

    // ── HandleBadgePoints Listener ──

    public function test_handle_badge_points_creates_performance_alert(): void
    {
        $user = User::factory()->create();

        // Create badge
        $badge = Badge::create([
            'name_ar' => 'شارة اختبار',
            'name_en' => 'Test Badge',
            'slug' => 'test-badge-' . uniqid(),
            'description_ar' => 'اختبار',
            'icon' => 'star',
            'category' => 'attendance',
            'criteria' => ['days' => 5],
            'points_reward' => 50,
        ]);

        $userBadge = UserBadge::create([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'awarded_at' => now(),
        ]);

        $event = new BadgeAwarded($userBadge);
        $listener = new HandleBadgePoints();
        $listener->handle($event);

        $this->assertDatabaseHas('performance_alerts', [
            'user_id' => $user->id,
            'alert_type' => 'badge_earned',
            'severity' => 'info',
        ]);
    }

    // ── HandleTrapTriggered Listener ──

    public function test_handle_trap_triggered_logs_event(): void
    {
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('warning')->once();

        $trap = new Trap(['trap_code' => 'TEST', 'name' => 'Test Trap']);
        $user = new User(['name_ar' => 'Test']);
        $user->id = 1;
        $user->employee_id = 'EMP-001';
        $interaction = new TrapInteraction([
            'risk_score' => 0.5,
            'action_taken' => 'logged',
            'ip_address' => '127.0.0.1',
            'interaction_count' => 1,
        ]);

        $event = new TrapTriggered($trap, $user, $interaction);
        $listener = new HandleTrapTriggered();
        $listener->handle($event);
    }

    public function test_handle_trap_triggered_escalation_logs_critical(): void
    {
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('warning')->once();
        Log::shouldReceive('critical')->once();

        $trap = new Trap(['trap_code' => 'TEST', 'name' => 'Test Trap']);
        $user = new User(['name_ar' => 'Test']);
        $user->id = 1;
        $user->employee_id = 'EMP-001';
        $interaction = new TrapInteraction([
            'risk_score' => 0.9,
            'action_taken' => 'escalated',
            'ip_address' => '127.0.0.1',
            'interaction_count' => 5,
        ]);

        $event = new TrapTriggered($trap, $user, $interaction);
        $listener = new HandleTrapTriggered();
        $listener->handle($event);
    }

    // ── Event Dispatching ──

    public function test_events_can_be_dispatched(): void
    {
        Event::fake();

        Event::dispatch(new AttendanceRecorded(new AttendanceLog()));
        Event::dispatch(new BadgeAwarded(new UserBadge()));

        Event::assertDispatched(AttendanceRecorded::class);
        Event::assertDispatched(BadgeAwarded::class);
    }
}
