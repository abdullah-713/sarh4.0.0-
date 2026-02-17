<?php

namespace Database\Seeders;

use App\Models\UserShift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * SarhIndex v3.4 — مصحح بيانات user_shifts
 *
 * يملأ الأعمدة الجديدة (assigned_by, approved_by, approved_at, reason)
 * في السجلات الموجودة مسبقاً قبل ترقية الموديل.
 *
 * ⚠️ يُشغّل مرة واحدة فقط بعد تشغيل المايجريشن.
 * ⚠️ لا يمسح أي بيانات — فقط يضيف قيم للأعمدة الجديدة الفارغة.
 */
class FixUserShiftsDataSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminId = DB::table('users')
            ->where('is_super_admin', true)
            ->value('id') ?? 1;

        // 1. ملء أعمدة التدقيق في user_shifts
        $updated = DB::table('user_shifts')
            ->whereNull('assigned_by')
            ->update([
                'assigned_by' => $superAdminId,
                'approved_by' => $superAdminId,
                'approved_at' => now(),
                'reason'      => 'ترحيل تلقائي من النظام القديم (v3.4)',
            ]);

        $this->command->info("✅ user_shifts: تم تحديث {$updated} سجل بأعمدة التدقيق.");

        // 2. ملء عمود awarded_by في user_badges
        $badgesUpdated = DB::table('user_badges')
            ->whereNull('awarded_by')
            ->update([
                'awarded_by' => $superAdminId,
            ]);

        $this->command->info("✅ user_badges: تم تحديث {$badgesUpdated} سجل بعمود awarded_by.");

        // 3. ملء effective_from من created_at (v4.0)
        $effectiveUpdated = DB::table('user_shifts')
            ->whereNull('effective_from')
            ->update(['effective_from' => DB::raw('created_at')]);

        $this->command->info("✅ user_shifts: تم تحديث {$effectiveUpdated} سجل بعمود effective_from.");

        // 4. تعيين أحدث تعيين كـ is_current لكل موظف (v4.0)
        $userIds = DB::table('user_shifts')->distinct()->pluck('user_id');
        $currentUpdated = 0;

        foreach ($userIds as $userId) {
            // إعادة تعيين الكل كـ false
            DB::table('user_shifts')
                ->where('user_id', $userId)
                ->update(['is_current' => false]);

            // تعيين الأحدث كـ true
            $latest = DB::table('user_shifts')
                ->where('user_id', $userId)
                ->orderBy('effective_from', 'desc')
                ->first();

            if ($latest) {
                DB::table('user_shifts')
                    ->where('id', $latest->id)
                    ->update(['is_current' => true]);
                $currentUpdated++;
            }
        }

        $this->command->info("✅ user_shifts: تم تعيين is_current لـ {$currentUpdated} موظف.");
    }
}
