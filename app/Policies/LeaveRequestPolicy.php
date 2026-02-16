<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    /**
     * جميع الموظفين يمكنهم رؤية قائمة الإجازات (مع تصفية حسب الفرع في Resource).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * رؤية طلب إجازة محدد: صاحبه، مديره المباشر، أو مدير فرعه.
     */
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        // صاحب الطلب
        if ($user->id === $leaveRequest->user_id) {
            return true;
        }

        // المدير المباشر
        $owner = $leaveRequest->user;
        if ($owner && $owner->direct_manager_id === $user->id) {
            return true;
        }

        // مدير الفرع (المستوى 6+)
        if ($user->security_level >= 6 && $owner && $user->branch_id === $owner->branch_id) {
            return true;
        }

        return false;
    }

    /**
     * إنشاء طلب إجازة: جميع الموظفين.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * تعديل طلب إجازة: صاحبه (إذا لم يُعتمد)، أو المستوى 6+ لفرعه.
     */
    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        // صاحب الطلب يعدّل طلبه المعلّق فقط
        if ($user->id === $leaveRequest->user_id && $leaveRequest->status === 'pending') {
            return true;
        }

        // مدير الفرع (المستوى 6+)
        $owner = $leaveRequest->user;
        if ($user->security_level >= 6 && $owner && $user->branch_id === $owner->branch_id) {
            return true;
        }

        return false;
    }

    /**
     * حذف طلب إجازة: صاحبه (إذا معلّق)، أو المستوى 10.
     */
    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        return $user->id === $leaveRequest->user_id && $leaveRequest->status === 'pending';
    }
}
