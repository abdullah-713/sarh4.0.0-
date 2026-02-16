<?php

namespace App\Policies;

use App\Models\Payroll;
use App\Models\User;

class PayrollPolicy
{
    /**
     * المستوى 7+ أو super_admin يمكنهم رؤية كشوف الرواتب.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 7;
    }

    /**
     * رؤية كشف راتب محدد: صاحبه أو مدير فرعه أو المستوى 7+.
     */
    public function view(User $user, Payroll $payroll): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        // الموظف يرى راتبه
        if ($user->id === $payroll->user_id) {
            return true;
        }

        // المستوى 7+ يرى رواتب فرعه
        if ($user->security_level >= 7 && $user->branch_id === $payroll->branch_id) {
            return true;
        }

        return false;
    }

    /**
     * إنشاء كشف راتب: المستوى 7+ فقط.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 7;
    }

    /**
     * تعديل كشف راتب: المستوى 7+ لفرعه، أو المستوى 10.
     */
    public function update(User $user, Payroll $payroll): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        return $user->security_level >= 7 && $user->branch_id === $payroll->branch_id;
    }

    /**
     * حذف كشف راتب: المستوى 10 فقط.
     */
    public function delete(User $user, Payroll $payroll): bool
    {
        return $user->is_super_admin || $user->security_level >= 10;
    }
}
