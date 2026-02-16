<?php

namespace App\Policies;

use App\Models\Holiday;
use App\Models\User;

class HolidayPolicy
{
    /**
     * جميع الموظفين يمكنهم رؤية العطل.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * رؤية عطلة محددة.
     */
    public function view(User $user, Holiday $holiday): bool
    {
        return true;
    }

    /**
     * إنشاء عطلة: المستوى 6+ فقط.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 6;
    }

    /**
     * تعديل عطلة: المستوى 6+ لفرعه، أو المستوى 10.
     */
    public function update(User $user, Holiday $holiday): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        // عطلة عامة (بدون فرع) — المستوى 10 فقط
        if ($holiday->branch_id === null) {
            return false;
        }

        return $user->security_level >= 6 && $user->branch_id === $holiday->branch_id;
    }

    /**
     * حذف عطلة: المستوى 10 فقط.
     */
    public function delete(User $user, Holiday $holiday): bool
    {
        return $user->is_super_admin || $user->security_level >= 10;
    }
}
