<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    /**
     * جميع الموظفين يمكنهم رؤية الفروع.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * رؤية فرع محدد.
     */
    public function view(User $user, Branch $branch): bool
    {
        return true;
    }

    /**
     * إنشاء فرع: المستوى 10 فقط.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 10;
    }

    /**
     * تعديل فرع: المستوى 7+ لفرعه، أو المستوى 10.
     */
    public function update(User $user, Branch $branch): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        return $user->security_level >= 7 && $user->branch_id === $branch->id;
    }

    /**
     * حذف فرع: المستوى 10 فقط.
     */
    public function delete(User $user, Branch $branch): bool
    {
        return $user->is_super_admin || $user->security_level >= 10;
    }
}
