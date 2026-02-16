<?php

namespace App\Policies;

use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    /**
     * جميع الموظفين يمكنهم رؤية المناوبات.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * رؤية مناوبة محددة.
     */
    public function view(User $user, Shift $shift): bool
    {
        return true;
    }

    /**
     * إنشاء مناوبة: المستوى 7+ فقط.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 7;
    }

    /**
     * تعديل مناوبة: المستوى 7+ فقط.
     */
    public function update(User $user, Shift $shift): bool
    {
        return $user->is_super_admin || $user->security_level >= 7;
    }

    /**
     * حذف مناوبة: المستوى 10 فقط.
     */
    public function delete(User $user, Shift $shift): bool
    {
        return $user->is_super_admin || $user->security_level >= 10;
    }
}
