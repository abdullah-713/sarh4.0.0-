<?php

namespace App\Filament\App\Pages;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\User;
use Filament\Pages\Page;

class BranchLeaderboardPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?int $navigationSort = -3; // Before dashboard (-2)

    protected static string $view = 'filament.app.pages.branch-leaderboard';

    protected static ?string $slug = 'branch-leaderboard';

    public static function getNavigationLabel(): string
    {
        return __('competition.leaderboard_title');
    }

    public function getTitle(): string
    {
        return __('competition.leaderboard_title');
    }

    /**
     * Rank branches by LOWEST total financial loss (cost-per-minute * total delay).
     */
    public function getBranches(): array
    {
        $branches = Branch::where('is_active', true)->get();
        $startOfMonth = now()->startOfMonth();
        $today = now();

        $ranked = [];

        foreach ($branches as $branch) {
            $employees = User::where('branch_id', $branch->id)
                ->where('status', 'active')
                ->get();

            $employeeCount = $employees->count();
            if ($employeeCount === 0) continue;

            // Total delay minutes this month for the branch
            $totalDelay = AttendanceLog::where('branch_id', $branch->id)
                ->whereBetween('attendance_date', [$startOfMonth->toDateString(), $today->toDateString()])
                ->where('delay_minutes', '>', 0)
                ->sum('delay_minutes');

            // Late check-ins count
            $lateCheckins = AttendanceLog::where('branch_id', $branch->id)
                ->whereBetween('attendance_date', [$startOfMonth->toDateString(), $today->toDateString()])
                ->where('delay_minutes', '>', 0)
                ->count();

            // Financial loss = sum of each employee's cost_per_minute * their delay
            $totalLoss = 0;
            foreach ($employees as $emp) {
                $monthlySalary = $emp->basic_salary + ($emp->housing_allowance ?? 0)
                    + ($emp->transport_allowance ?? 0) + ($emp->other_allowances ?? 0);
                $workingMinutes = ($emp->working_days_per_month ?: 22) * ($emp->working_hours_per_day ?: 3) * 60;
                $costPerMinute = $workingMinutes > 0 ? $monthlySalary / $workingMinutes : 0;

                $empDelay = AttendanceLog::where('user_id', $emp->id)
                    ->whereBetween('attendance_date', [$startOfMonth->toDateString(), $today->toDateString()])
                    ->where('delay_minutes', '>', 0)
                    ->sum('delay_minutes');

                $totalLoss += round($costPerMinute * $empDelay, 2);
            }

            // Perfect employees (zero lates)
            $perfectEmployees = 0;
            foreach ($employees as $emp) {
                $hasLate = AttendanceLog::where('user_id', $emp->id)
                    ->whereBetween('attendance_date', [$startOfMonth->toDateString(), $today->toDateString()])
                    ->where('delay_minutes', '>', 0)
                    ->exists();
                if (!$hasLate) $perfectEmployees++;
            }

            $totalPoints = $employees->sum('total_points');

            // Level based on total_loss
            $level = match (true) {
                $totalLoss == 0   => ['name' => __('competition.level_legendary'), 'icon' => "\u{1F3C6}", 'color' => 'text-yellow-500', 'bg' => 'bg-yellow-50 border-yellow-300'],
                $totalLoss < 500  => ['name' => __('competition.level_diamond'),   'icon' => "\u{1F48E}", 'color' => 'text-blue-500',   'bg' => 'bg-blue-50 border-blue-300'],
                $totalLoss < 1500 => ['name' => __('competition.level_gold'),      'icon' => "\u{1F947}", 'color' => 'text-amber-500',  'bg' => 'bg-amber-50 border-amber-300'],
                $totalLoss < 3000 => ['name' => __('competition.level_silver'),    'icon' => "\u{1F948}", 'color' => 'text-gray-400',   'bg' => 'bg-gray-50 border-gray-300'],
                $totalLoss < 5000 => ['name' => __('competition.level_bronze'),    'icon' => "\u{1F949}", 'color' => 'text-orange-600', 'bg' => 'bg-orange-50 border-orange-300'],
                default            => ['name' => __('competition.level_starter'),   'icon' => "\u{1F422}", 'color' => 'text-red-500',    'bg' => 'bg-red-50 border-red-300'],
            };

            $ranked[] = [
                'branch'            => $branch,
                'total_loss'        => $totalLoss,
                'total_delay'       => $totalDelay,
                'level'             => $level,
                'employee_count'    => $employeeCount,
                'late_checkins'     => $lateCheckins,
                'perfect_employees' => $perfectEmployees,
                'total_points'      => $totalPoints,
            ];
        }

        // Sort by LOWEST total_loss (best = least money wasted)
        usort($ranked, fn ($a, $b) => $a['total_loss'] <=> $b['total_loss']);

        foreach ($ranked as $i => &$item) {
            $item['rank'] = $i + 1;
            if ($i === 0) {
                $item['badge'] = "\u{1F3C6}";
                $item['badge_label'] = __('competition.trophy_winner');
            } elseif ($i === count($ranked) - 1 && count($ranked) > 1) {
                $item['badge'] = "\u{1F422}";
                $item['badge_label'] = __('competition.turtle_last');
            } else {
                $item['badge'] = '';
                $item['badge_label'] = '';
            }
        }

        return $ranked;
    }
}
