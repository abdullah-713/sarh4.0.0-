<?php

namespace App\Listeners;

use App\Events\TrapTriggered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * معالج حدث تفعيل الفخ — يُنفذ عند إطلاق TrapTriggered
 *
 * المسؤوليات:
 * 1. تسجيل الحدث في اللوق
 * 2. إرسال تنبيه للمدير (L10) عند التصعيد
 */
class HandleTrapTriggered
{
    public function handle(TrapTriggered $event): void
    {
        // تسجيل الحدث
        Log::channel('daily')->warning('🪤 Trap triggered', [
            'trap_code'   => $event->trap->trap_code,
            'trap_name'   => $event->trap->name,
            'user_id'     => $event->user->id,
            'user_name'   => $event->user->name,
            'risk_score'  => $event->interaction->risk_score,
            'action'      => $event->interaction->action_taken,
            'ip'          => $event->interaction->ip_address,
            'count'       => $event->interaction->interaction_count,
        ]);

        // تنبيه فوري عند التصعيد
        if ($event->interaction->action_taken === 'escalated') {
            Log::channel('daily')->critical('🚨 TRAP ESCALATION', [
                'trap_code'  => $event->trap->trap_code,
                'user'       => $event->user->name . ' (#' . $event->user->employee_id . ')',
                'risk_score' => $event->interaction->risk_score,
                'total_interactions' => $event->interaction->interaction_count,
            ]);
        }
    }
}
