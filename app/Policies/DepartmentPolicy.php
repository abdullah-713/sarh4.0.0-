<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    /**
     * جميع الموظفين يمكنهم رؤية الأقسام.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * رؤية قسم محدد.
     */
    public function view(User $user, Department $department): bool
    {
        return true;
    }

    /**
     * إنشاء قسم: المستوى 7+ فقط.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 7;
    }

    /**
     * تعديل قسم: المستوى 7+ لفرعه، أو المستوى 10.
     */
    public function update(User $user, Department $department): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        return $user->security_level >= 7 && $user->branch_id === $department->branch_id;
    }

    /**
     * حذف قسم: المستوى 10 فقط.
     */
    public function delete(User $user, Department $department): bool
    {
        return $user->is_super_admin || $user->security_level >= 10;
    }
}
