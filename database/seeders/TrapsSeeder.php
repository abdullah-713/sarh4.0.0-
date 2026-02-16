<?php

namespace Database\Seeders;

use App\Models\Trap;
use Illuminate\Database\Seeder;

class TrapsSeeder extends Seeder
{
    public function run(): void
    {
        $traps = [
            [
                'trap_code'    => 'SALARY_PEEK',
                'name'         => 'استعراض رواتب الزملاء',
                'description'  => 'زر مموّه يوحي بالقدرة على عرض رواتب الموظفين الآخرين. يُفعَّل عند نقر المستخدم عليه.',
                'trigger_type' => 'button_click',
                'risk_weight'  => 2.0,
                'is_active'    => true,
                'placement'    => 'sidebar',
                'target_levels' => [1, 2, 3, 4, 5],
                'fake_response' => [
                    'status'  => 'success',
                    'message' => 'جارٍ تحميل البيانات...',
                ],
            ],
            [
                'trap_code'    => 'EDIT_ATTENDANCE',
                'name'         => 'تعديل سجل الحضور',
                'description'  => 'نموذج مموّه لتعديل سجلات الحضور. يُسجَّل عند محاولة الإرسال.',
                'trigger_type' => 'form_submit',
                'risk_weight'  => 3.5,
                'is_active'    => true,
                'placement'    => 'dashboard',
                'target_levels' => [1, 2, 3, 4, 5, 6],
                'fake_response' => [
                    'status'  => 'success',
                    'message' => 'تم حفظ التعديلات بنجاح',
                ],
            ],
            [
                'trap_code'     => 'SYSTEM_BYPASS',
                'name'          => 'تجاوز نظام الحماية',
                'description'   => 'رابط مموّه يوحي بإمكانية تجاوز القيود الأمنية.',
                'trigger_type'  => 'page_visit',
                'risk_weight'   => 5.0,
                'is_active'     => true,
                'placement'     => 'settings',
                'target_levels' => [1, 2, 3, 4, 5, 6, 7],
                'fake_response' => [
                    'status'  => 'error',
                    'message' => 'خطأ في الخادم. يرجى المحاولة لاحقاً.',
                ],
            ],
            [
                'trap_code'     => 'DATA_EXPORT',
                'name'          => 'تصدير بيانات سرية',
                'description'   => 'زر تصدير مموّه يوحي بالقدرة على تحميل بيانات حساسة.',
                'trigger_type'  => 'data_export',
                'risk_weight'   => 4.0,
                'is_active'     => true,
                'placement'     => 'toolbar',
                'target_levels' => [1, 2, 3, 4, 5, 6, 7, 8],
                'fake_response' => [
                    'status'  => 'success',
                    'message' => 'جارٍ إعداد الملف للتحميل...',
                ],
            ],
        ];

        foreach ($traps as $trap) {
            Trap::updateOrCreate(
                ['trap_code' => $trap['trap_code']],
                $trap
            );
        }
    }
}
