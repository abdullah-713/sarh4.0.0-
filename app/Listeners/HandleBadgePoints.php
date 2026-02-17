<?php

namespace App\Listeners;

use App\Events\BadgeAwarded;
use App\Models\PerformanceAlert;
use Illuminate\Support\Facades\Log;

class HandleBadgePoints
{
    public function handle(BadgeAwarded $event): void
    {
        $userBadge = $event->userBadge;

        try {
            PerformanceAlert::create([
                'user_id'    => $userBadge->user_id,
                'alert_type' => 'badge_earned',
                'severity'   => 'info',
                'title_ar'   => 'تهانينا!',
                'title_en'   => 'Congratulations!',
                'message_ar' => "لقد حصلت على شارة {$userBadge->badge->name}",
                'message_en' => "You have earned the {$userBadge->badge->name} badge",
                'trigger_data' => [
                    'badge_id'      => $userBadge->badge_id,
                    'user_badge_id' => $userBadge->id,
                    'points_reward' => $userBadge->badge->points_reward ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('HandleBadgePoints: فشل إنشاء التنبيه', [
                'user_badge_id' => $userBadge->id,
                'error'         => $e->getMessage(),
            ]);
        }
    }
}
