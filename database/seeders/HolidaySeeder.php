<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

/**
 * بذر العطلات الرسمية في المملكة العربية السعودية
 * يشمل عطلات 2026 + العطلات المتكررة سنوياً
 */
class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('  بذر العطلات الرسمية...');

        $holidays = [
            // ═══════════════════════════════════════════════════
            // العطلات المتكررة (recurring) — تتكرر كل سنة
            // ═══════════════════════════════════════════════════
            [
                'name_ar' => 'اليوم الوطني السعودي',
                'name_en' => 'Saudi National Day',
                'date' => '2026-09-23',
                'type' => 'national',
                'is_recurring' => true,
            ],
            [
                'name_ar' => 'يوم التأسيس',
                'name_en' => 'Founding Day',
                'date' => '2026-02-22',
                'type' => 'national',
                'is_recurring' => true,
            ],

            // ═══════════════════════════════════════════════════
            // عطلات 2026 (إجازة عيد الفطر — تقديرية)
            // ═══════════════════════════════════════════════════
            [
                'name_ar' => 'إجازة عيد الفطر',
                'name_en' => 'Eid Al-Fitr Holiday',
                'date' => '2026-03-20',
                'type' => 'religious',
                'is_recurring' => false,
            ],
            [
                'name_ar' => 'إجازة عيد الفطر',
                'name_en' => 'Eid Al-Fitr Holiday',
                'date' => '2026-03-21',
                'type' => 'religious',
                'is_recurring' => false,
            ],
            [
                'name_ar' => 'إجازة عيد الفطر',
                'name_en' => 'Eid Al-Fitr Holiday',
                'date' => '2026-03-22',
                'type' => 'religious',
                'is_recurring' => false,
            ],
            [
                'name_ar' => 'إجازة عيد الفطر',
                'name_en' => 'Eid Al-Fitr Holiday',
                'date' => '2026-03-23',
                'type' => 'religious',
                'is_recurring' => false,
            ],

            // ═══════════════════════════════════════════════════
            // إجازة يوم عرفة + عيد الأضحى 2026 (تقديرية)
            // ═══════════════════════════════════════════════════
            [
                'name_ar' => 'يوم عرفة',
                'name_en' => 'Day of Arafah',
                'date' => '2026-05-26',
                'type' => 'religious',
                'is_recurring' => false,
            ],
            [
                'name_ar' => 'إجازة عيد الأضحى',
                'name_en' => 'Eid Al-Adha Holiday',
                'date' => '2026-05-27',
                'type' => 'religious',
                'is_recurring' => false,
            ],
            [
                'name_ar' => 'إجازة عيد الأضحى',
                'name_en' => 'Eid Al-Adha Holiday',
                'date' => '2026-05-28',
                'type' => 'religious',
                'is_recurring' => false,
            ],
            [
                'name_ar' => 'إجازة عيد الأضحى',
                'name_en' => 'Eid Al-Adha Holiday',
                'date' => '2026-05-29',
                'type' => 'religious',
                'is_recurring' => false,
            ],

            // ═══════════════════════════════════════════════════
            // عطلات إضافية 2026
            // ═══════════════════════════════════════════════════
            [
                'name_ar' => 'إجازة نهاية الأسبوع الممتدة (عيد الفطر)',
                'name_en' => 'Extended Weekend (Eid Al-Fitr)',
                'date' => '2026-03-24',
                'type' => 'national',
                'is_recurring' => false,
            ],
        ];

        $created = 0;
        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(
                [
                    'name_en' => $holiday['name_en'],
                    'date' => $holiday['date'],
                ],
                array_merge($holiday, [
                    'branch_id' => null, // عطلة عامة لجميع الفروع
                ])
            );
            $created++;
        }

        $this->command->info("  ✓ {$created} عطلة رسمية");
    }
}
