<?php

namespace App\Policies;

use App\Models\Circular;
use App\Models\User;

class CircularPolicy
{
    /**
     * جميع الموظفين يمكنهم رؤية التعاميم.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * رؤية تعميم محدد: الجميع (التعاميم عامة).
     */
    public function view(User $user, Circular $circular): bool
    {
        return true;
    }

    /**
     * إنشاء تعميم: المستوى 6+ فقط.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 6;
    }

    /**
     * تعديل تعميم: منشئه أو المستوى 7+.
     */
    public function update(User $user, Circular $circular): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        // منشئ التعميم
        if ($user->id === $circular->created_by) {
            return true;
        }

        return $user->security_level >= 7;
    }

    /**
     * حذف تعميم: المستوى 10 فقط.
     */
    public function delete(User $user, Circular $circular): bool
    {
        return $user->is_super_admin || $user->security_level >= 10;
    }
}
