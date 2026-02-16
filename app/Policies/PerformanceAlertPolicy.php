<?php

namespace App\Policies;

use App\Models\PerformanceAlert;
use App\Models\User;

class PerformanceAlertPolicy
{
    /**
     * المستوى 5+ يمكنه رؤية تنبيهات الأداء.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 5;
    }

    /**
     * رؤية تنبيه محدد: صاحبه، أو المستوى 5+ لفرعه.
     */
    public function view(User $user, PerformanceAlert $alert): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        // صاحب التنبيه
        if ($user->id === $alert->user_id) {
            return true;
        }

        // المستوى 5+ لفرعه
        $owner = $alert->user;
        if ($user->security_level >= 5 && $owner && $user->branch_id === $owner->branch_id) {
            return true;
        }

        return false;
    }

    /**
     * إنشاء تنبيه: المستوى 5+ فقط.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 5;
    }

    /**
     * تعديل تنبيه: المستوى 7+ لفرعه، أو المستوى 10.
     */
    public function update(User $user, PerformanceAlert $alert): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        $owner = $alert->user;
        return $user->security_level >= 7 && $owner && $user->branch_id === $owner->branch_id;
    }

    /**
     * حذف تنبيه: المستوى 10 فقط.
     */
    public function delete(User $user, PerformanceAlert $alert): bool
    {
        return $user->is_super_admin || $user->security_level >= 10;
    }
}
