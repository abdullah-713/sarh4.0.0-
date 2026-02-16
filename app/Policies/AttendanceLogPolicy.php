<?php

namespace App\Policies;

use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AttendanceLogPolicy
{
    /**
     * هل يستطيع المستخدم مشاهدة قائمة سجلات الحضور؟
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * هل يستطيع المستخدم مشاهدة هذا السجل؟
     */
    public function view(User $user, AttendanceLog $log): bool
    {
        // المستوى 10 يرى كل شيء
        if ($user->security_level === 10 || $user->is_super_admin) {
            return true;
        }

        // الموظف يرى سجلاته الشخصية
        if ($user->id === $log->user_id) {
            return true;
        }

        // المدير المباشر يرى سجلات مرؤوسيه
        $logOwner = $log->user;
        if ($logOwner && $logOwner->direct_manager_id === $user->id) {
            return true;
        }

        // غيره يرى فقط سجلات فرعه (المستوى 6+)
        if ($user->security_level >= 6 && $user->branch_id === $log->branch_id) {
            return true;
        }

        return false;
    }

    /**
     * تصفية الاستعلامات حسب الفرع - تستخدم في Resources
     */
    public static function scopeBranch(Builder $query, User $user): Builder
    {
        if ($user->security_level === 10 || $user->is_super_admin) {
            return $query;
        }

        return $query->where('branch_id', $user->branch_id);
    }

    /**
     * إنشاء سجل حضور: المستوى 6+ فقط.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 6;
    }

    /**
     * تعديل سجل حضور: المستوى 7+ لفرعه، أو المستوى 10.
     */
    public function update(User $user, AttendanceLog $log): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        return $user->security_level >= 7 && $user->branch_id === $log->branch_id;
    }

    /**
     * حذف سجل حضور: المستوى 10 فقط.
     */
    public function delete(User $user, AttendanceLog $log): bool
    {
        return $user->is_super_admin || $user->security_level >= 10;
    }
}
