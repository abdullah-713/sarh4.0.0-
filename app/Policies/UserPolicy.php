<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * هل يستطيع المستخدم مشاهدة قائمة الموظفين؟
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * هل يستطيع المستخدم مشاهدة هذا الموظف؟
     */
    public function view(User $user, User $target): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        // الموظف يرى نفسه
        if ($user->id === $target->id) {
            return true;
        }

        // المدير المباشر
        if ($target->direct_manager_id === $user->id) {
            return true;
        }

        // مدير الفرع (المستوى 6+)
        if ($user->security_level >= 6 && $user->branch_id === $target->branch_id) {
            return true;
        }

        return false;
    }

    /**
     * إنشاء موظف: المستوى 7+ فقط.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 7;
    }

    /**
     * تعديل موظف: المستوى 6+ لفرعه، أو المستوى 10.
     */
    public function update(User $user, User $target): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        // مدير الفرع يعدّل موظفي فرعه
        if ($user->security_level >= 6 && $user->branch_id === $target->branch_id) {
            return true;
        }

        return false;
    }

    /**
     * هل يستطيع هذا المستخدم مشاهدة راتب الموظف المستهدف؟
     */
    public function viewSalary(User $user, User $target): bool
    {
        // المستوى 10 يرى كل شيء
        if ($user->security_level === 10 || $user->is_super_admin) {
            return true;
        }

        // المدير المباشر يرى راتب مرؤوسه
        if ($target->direct_manager_id === $user->id) {
            return true;
        }

        // مدير الفرع يرى رواتب فرعه (المستوى 7 فأعلى)
        if ($user->security_level >= 7 && $user->branch_id === $target->branch_id) {
            return true;
        }

        // مدير القسم يرى رواتب قسمه (المستوى 6 فأعلى)
        if ($user->security_level >= 6 && $user->department_id === $target->department_id) {
            return true;
        }

        return false;
    }

    /**
     * هل يستطيع هذا المستخدم تعديل راتب الموظف؟
     */
    public function updateSalary(User $user, User $target): bool
    {
        // فقط المستوى 10 أو مدير الفرع
        if ($user->security_level === 10 || $user->is_super_admin) {
            return true;
        }

        // مدير الفرع (المستوى 7+) يعدل رواتب فرعه
        if ($user->security_level >= 7 && $user->branch_id === $target->branch_id) {
            return true;
        }

        return false;
    }

    /**
     * هل يستطيع هذا المستخدم حذف الموظف؟
     */
    public function delete(User $user, User $target): bool
    {
        // فقط المستوى 10
        return $user->security_level === 10 || $user->is_super_admin;
    }
}
