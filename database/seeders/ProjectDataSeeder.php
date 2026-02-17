<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProjectDataSeeder extends Seeder
{
    /**
     * SarhIndex v1.7.0 — Real branch & employee data.
     * Idempotent via updateOrCreate on code/email.
     */
    public function run(): void
    {
        $password = Hash::make(env('SEED_PASSWORD', 'ChangeMe123!!'));

        /*
        |----------------------------------------------------------------------
        | 1. Branches — 5 Real Locations (17m Geofence)
        |----------------------------------------------------------------------
        */
        $branches = [
            [
                'code'                 => 'SI-HQ',
                'name_ar'              => 'مؤشر صرح الرئيسي',
                'name_en'              => 'SarhIndex HQ',
                'city_ar'              => 'الرياض',
                'city_en'              => 'Riyadh',
                'address_ar'           => 'مؤشر صرح — المقر الرئيسي',
                'address_en'           => 'SarhIndex — Headquarters',
                'latitude'             => 24.572368,
                'longitude'            => 46.602829,
                'geofence_radius'      => 17,
                'default_shift_start'  => '08:00',
                'default_shift_end'    => '17:00',
                'grace_period_minutes' => 15,
                'monthly_salary_budget'=> 100000,
                'is_active'            => true,
            ],
            [
                'code'                 => 'SI-CORNER',
                'name_ar'              => 'مؤشر صرح كورنر',
                'name_en'              => 'SarhIndex Corner',
                'city_ar'              => 'الرياض',
                'city_en'              => 'Riyadh',
                'address_ar'           => 'مؤشر صرح — كورنر',
                'address_en'           => 'SarhIndex — Corner',
                'latitude'             => 24.572439,
                'longitude'            => 46.603008,
                'geofence_radius'      => 17,
                'default_shift_start'  => '08:00',
                'default_shift_end'    => '17:00',
                'grace_period_minutes' => 15,
                'monthly_salary_budget'=> 120000,
                'is_active'            => true,
            ],
            [
                'code'                 => 'SI-2',
                'name_ar'              => 'مؤشر صرح 2',
                'name_en'              => 'SarhIndex 2',
                'city_ar'              => 'الرياض',
                'city_en'              => 'Riyadh',
                'address_ar'           => 'مؤشر صرح — الفرع الثاني',
                'address_en'           => 'SarhIndex — Branch 2',
                'latitude'             => 24.572262,
                'longitude'            => 46.602580,
                'geofence_radius'      => 17,
                'default_shift_start'  => '08:00',
                'default_shift_end'    => '17:00',
                'grace_period_minutes' => 15,
                'monthly_salary_budget'=> 80000,
                'is_active'            => true,
            ],
            [
                'code'                 => 'FADA-1',
                'name_ar'              => 'فضاء المحركات 1',
                'name_en'              => 'Fada Al-Muharrikat 1',
                'city_ar'              => 'الرياض',
                'city_en'              => 'Riyadh',
                'address_ar'           => 'فضاء المحركات — الفرع الأول',
                'address_en'           => 'Fada Al-Muharrikat — Branch 1',
                'latitude'             => 24.56968126,
                'longitude'            => 46.61405911,
                'geofence_radius'      => 17,
                'default_shift_start'  => '08:00',
                'default_shift_end'    => '17:00',
                'grace_period_minutes' => 15,
                'monthly_salary_budget'=> 150000,
                'is_active'            => true,
            ],
            [
                'code'                 => 'FADA-2',
                'name_ar'              => 'فضاء المحركات 2',
                'name_en'              => 'Fada Al-Muharrikat 2',
                'city_ar'              => 'الرياض',
                'city_en'              => 'Riyadh',
                'address_ar'           => 'فضاء المحركات — الفرع الثاني',
                'address_en'           => 'Fada Al-Muharrikat — Branch 2',
                'latitude'             => 24.566088,
                'longitude'            => 46.621759,
                'geofence_radius'      => 17,
                'default_shift_start'  => '08:00',
                'default_shift_end'    => '17:00',
                'grace_period_minutes' => 15,
                'monthly_salary_budget'=> 200000,
                'is_active'            => true,
            ],
        ];

        $branchModels = [];
        foreach ($branches as $branchData) {
            $branchModels[$branchData['code']] = Branch::updateOrCreate(
                ['code' => $branchData['code']],
                $branchData
            );
        }

        /*
        |----------------------------------------------------------------------
        | 2. Super Admin — Level 10 God Mode (Abdullah)
        |----------------------------------------------------------------------
        */
        $admin = User::updateOrCreate(
            ['email' => 'abdullah@sarh.app'],
            [
                'name_ar'                => 'عبدالله',
                'name_en'                => 'Abdullah',
                'employee_id'            => 'emp001',
                'password'               => $password,
                'basic_salary'           => 45000,
                'housing_allowance'      => 11250,
                'transport_allowance'    => 3000,
                'branch_id'              => $branchModels['SI-HQ']->id,
                'working_days_per_month' => 22,
                'working_hours_per_day'  => 8,
                'status'                 => 'active',
                'employment_type'        => 'full_time',
                'locale'                 => 'ar',
                'timezone'               => 'Asia/Riyadh',
                'total_points'           => 500,
            ]
        );
        // security_level & is_super_admin are guarded — must forceFill
        $admin->forceFill(['security_level' => 10, 'is_super_admin' => true])->save();

        /*
        |----------------------------------------------------------------------
        | 3. Employees — Exact Mapping per Directive
        |----------------------------------------------------------------------
        */
        $employees = [
            // FADA-1 — 8
            ['عباس علي رمضان',    'Abbas Ali Ramadan',    'abbas@sarh.app',       'emp010', 4000, 'FADA-1'],
            ['عبدالهادي يونس',    'Abdulhadi Younis',     'abdulhadi@sarh.app',   'emp012', 4000, 'FADA-1'],
            ['محمد أفريدي',       'Mohammed Afridi',      'afridi@sarh.app',      'emp027', 4000, 'FADA-1'],
            ['محمد بلال',         'Mohammed Bilal',       'bilal.m@sarh.app',     'emp028', 4000, 'FADA-1'],
            ['محمد جلال',         'Mohammed Jalal',       'jalal@sarh.app',       'emp029', 4000, 'FADA-1'],
            ['منذر محمد',         'Munther Mohammed',     'munther@sarh.app',     'emp031', 4000, 'FADA-1'],
            ['مصطفى عوض سعد',    'Mustafa Awad Saad',    'mustafa@sarh.app',     'emp033', 4000, 'FADA-1'],
            ['سالفادور ديلا',     'Salvador Dela',        'salvador@sarh.app',    'emp038', 4000, 'FADA-1'],
            // SI-CORNER — 7
            ['أباوي',             'Abawe',                'abawe@sarh.app',       'emp009', 6000, 'SI-CORNER'],
            ['أرنوس',             'Arnous',               'arnous@sarh.app',      'emp016', 5000, 'SI-CORNER'],
            ['بلال',              'Bilal',                'bilal@sarh.app',       'emp018', 5000, 'SI-CORNER'],
            ['إيناي يو إس',      'Inay_us',              'inay.us@sarh.app',     'emp023', 5000, 'SI-CORNER'],
            ['مصعب',              'Musab',                'musab@sarh.app',       'emp032', 8000, 'SI-CORNER'],
            ['شعبان',             'Shaaban',              'shaaban@sarh.app',     'emp039', 5000, 'SI-CORNER'],
            ['وقاص',              'Wakas',                'wakas@sarh.app',       'emp041', 5000, 'SI-CORNER'],
            // SI-2 — 5
            ['أبو سليمان',        'Abu Suleiman',         'abu.suleiman@sarh.app','emp013', 4500, 'SI-2'],
            ['بخاري',             'Bukhari',              'bukhari@sarh.app',     'emp019', 4500, 'SI-2'],
            ['إسلام',             'Islam',                'islam@sarh.app',       'emp025', 4500, 'SI-2'],
            ['محسن',              'Mohsen',               'mohsen@sarh.app',      'emp030', 4500, 'SI-2'],
            ['صابر',              'Saber',                'saber@sarh.app',       'emp037', 4500, 'SI-2'],
            // SI-HQ — 4
            ['أمجد',              'Amjad',                'amjad@sarh.app',       'emp015', 4000, 'SI-HQ'],
            ['أيمن',              'Ayman',                'ayman@sarh.app',       'emp017', 4000, 'SI-HQ'],
            ['نجيب',              'Najeeb',               'najeeb@sarh.app',      'emp034', 4000, 'SI-HQ'],
            ['زاهر',              'Zaher',                'zaher@sarh.app',       'emp043', 4000, 'SI-HQ'],
            // FADA-2 — 11
            ['عبد واي',           'Abd_y',                'abd.y@sarh.app',       'emp011', 5500, 'FADA-2'],
            ['أفضل',              'Afzal',                'afzal@sarh.app',       'emp014', 5000, 'FADA-2'],
            ['حبيب',              'Habib',                'habib@sarh.app',       'emp020', 5000, 'FADA-2'],
            ['إمتي',              'Imti',                 'imti@sarh.app',        'emp021', 5000, 'FADA-2'],
            ['إيناي',             'Inay',                 'inay@sarh.app',        'emp022', 5000, 'FADA-2'],
            ['عرفان',             'Irfan',                'irfan@sarh.app',       'emp024', 5000, 'FADA-2'],
            ['جهاد',              'Jihad',                'jihad@sarh.app',       'emp026', 8000, 'FADA-2'],
            ['قتيبة',             'Qutaiba',              'qutaiba@sarh.app',     'emp035', 7000, 'FADA-2'],
            ['ريشا',              'Risha',                'risha@sarh.app',       'emp036', 5000, 'FADA-2'],
            ['شحاتة',             'Shehata',              'shehata@sarh.app',     'emp040', 6000, 'FADA-2'],
            ['وسيم',              'Wassim',               'wassim@sarh.app',      'emp042', 3500, 'FADA-2'],
        ];

        foreach ($employees as [$nameAr, $nameEn, $email, $empId, $salary, $branchCode]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name_ar'                => $nameAr,
                    'name_en'                => $nameEn,
                    'employee_id'            => $empId,
                    'password'               => $password,
                    'basic_salary'           => $salary,
                    'housing_allowance'      => round($salary * 0.25),
                    'transport_allowance'    => 1500,
                    'branch_id'              => $branchModels[$branchCode]->id,
                    'working_days_per_month' => 22,
                    'working_hours_per_day'  => 8,
                    'status'                 => 'active',
                    'employment_type'        => 'full_time',
                    'locale'                 => 'ar',
                    'timezone'               => 'Asia/Riyadh',
                    'total_points'           => 0,
                ]
            );
            // security_level is guarded — must forceFill
            $user->forceFill(['security_level' => 1, 'is_super_admin' => false])->save();
        }

        $this->command->info('ProjectDataSeeder: 5 branches (17m) + 1 super admin + 35 employees seeded.');
    }
}
