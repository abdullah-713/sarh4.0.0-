<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Seeder;

/**
 * بذر الأقسام الأساسية لشركة مؤشر صرح
 * يتم ربطها بالفروع الموجودة تلقائياً
 */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('  بذر الأقسام...');

        // الحصول على الفرع الرئيسي (SI-HQ) أو أول فرع
        $mainBranch = Branch::where('name_en', 'like', '%HQ%')
            ->orWhere('name_ar', 'like', '%الرئيسي%')
            ->first() ?? Branch::first();

        if (!$mainBranch) {
            $this->command->warn('  ⚠ لا توجد فروع — تخطي بذر الأقسام');
            return;
        }

        $departments = [
            [
                'name_ar' => 'الإدارة العامة',
                'name_en' => 'General Management',
                'code' => 'MGMT',
                'description_ar' => 'الإدارة العليا والتخطيط الاستراتيجي',
                'description_en' => 'Senior management and strategic planning',
            ],
            [
                'name_ar' => 'الموارد البشرية',
                'name_en' => 'Human Resources',
                'code' => 'HR',
                'description_ar' => 'شؤون الموظفين والتوظيف والتدريب',
                'description_en' => 'Personnel affairs, recruitment and training',
            ],
            [
                'name_ar' => 'المالية والمحاسبة',
                'name_en' => 'Finance & Accounting',
                'code' => 'FIN',
                'description_ar' => 'الشؤون المالية والرواتب والمحاسبة',
                'description_en' => 'Financial affairs, payroll and accounting',
            ],
            [
                'name_ar' => 'العمليات',
                'name_en' => 'Operations',
                'code' => 'OPS',
                'description_ar' => 'إدارة العمليات اليومية والإنتاج',
                'description_en' => 'Daily operations and production management',
            ],
            [
                'name_ar' => 'خدمة العملاء',
                'name_en' => 'Customer Service',
                'code' => 'CS',
                'description_ar' => 'خدمة العملاء والدعم الفني',
                'description_en' => 'Customer service and technical support',
            ],
            [
                'name_ar' => 'التقنية',
                'name_en' => 'IT & Technology',
                'code' => 'IT',
                'description_ar' => 'تقنية المعلومات والأنظمة',
                'description_en' => 'Information technology and systems',
            ],
            [
                'name_ar' => 'المبيعات والتسويق',
                'name_en' => 'Sales & Marketing',
                'code' => 'SALES',
                'description_ar' => 'المبيعات والتسويق والعلاقات العامة',
                'description_en' => 'Sales, marketing and public relations',
            ],
        ];

        $created = 0;
        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['code' => $dept['code']],
                array_merge($dept, [
                    'branch_id' => $mainBranch->id,
                    'is_active' => true,
                ])
            );
            $created++;
        }

        $this->command->info("  ✓ {$created} أقسام");
    }
}
