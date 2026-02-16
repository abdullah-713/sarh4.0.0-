<?php

namespace App\Policies;

use App\Models\EmployeeDocument;
use App\Models\User;

class EmployeeDocumentPolicy
{
    /**
     * جميع الموظفين يمكنهم رؤية قائمة الوثائق (مع تصفية في Resource).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * رؤية وثيقة محددة: صاحبها، أو المستوى 6+ لفرعه، أو المستوى 10.
     */
    public function view(User $user, EmployeeDocument $document): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        // صاحب الوثيقة
        if ($user->id === $document->user_id) {
            return true;
        }

        // مدير الفرع (المستوى 6+)
        $owner = $document->user;
        if ($user->security_level >= 6 && $owner && $user->branch_id === $owner->branch_id) {
            return true;
        }

        return false;
    }

    /**
     * إنشاء وثيقة: المستوى 6+ أو super_admin.
     */
    public function create(User $user): bool
    {
        return $user->is_super_admin || $user->security_level >= 6;
    }

    /**
     * تعديل وثيقة: المستوى 6+ لفرعه، أو المستوى 10.
     */
    public function update(User $user, EmployeeDocument $document): bool
    {
        if ($user->is_super_admin || $user->security_level >= 10) {
            return true;
        }

        $owner = $document->user;
        return $user->security_level >= 6 && $owner && $user->branch_id === $owner->branch_id;
    }

    /**
     * حذف وثيقة: المستوى 10 فقط.
     */
    public function delete(User $user, EmployeeDocument $document): bool
    {
        return $user->is_super_admin || $user->security_level >= 10;
    }
}
