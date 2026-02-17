<?php

namespace App\Livewire;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CompetitionWidget extends Component
{
    public array $topBranches = [];
    public ?array $myBranch = null;
    public int $myBranchRank = 0;
    public string $myBranchLevel = 'starter';
    public int $totalBranches = 0;

    public function mount(): void
    {
        $this->loadCompetitionData();
    }

    public function loadCompetitionData(): void
    {
        $user = Auth::user();
        $now = now();

        // Single query: get all branches with user counts
        $branches = Branch::active()
            ->withCount(['users' => fn ($q) => $q->active()])
            ->get();

        if ($branches->isEmpty()) {
            return;
        }

        $branchIds = $branches->pluck('id');

        // Single query: get all attendance logs for the month, grouped by branch
        $allLogs = AttendanceLog::whereIn('branch_id', $branchIds)
            ->whereMonth('attendance_date', $now->month)
            ->whereYear('attendance_date', $now->year)
            ->select('branch_id', 'status', 'user_id', 'delay_cost')
            ->get()
            ->groupBy('branch_id');

        // Single query: get employees with issues per branch
        $employeesWithIssues = AttendanceLog::whereIn('branch_id', $branchIds)
            ->whereMonth('attendance_date', $now->month)
            ->whereYear('attendance_date', $now->year)
            ->whereIn('status', ['late', 'absent'])
            ->select('branch_id', DB::raw('COUNT(DISTINCT user_id) as issue_count'))
            ->groupBy('branch_id')
            ->pluck('issue_count', 'branch_id');

        // Single query: get total points per branch
        $branchPoints = User::whereIn('branch_id', $branchIds)
            ->active()
            ->select('branch_id', DB::raw('SUM(total_points) as total'))
            ->groupBy('branch_id')
            ->pluck('total', 'branch_id');

        // Process in-memory — no more queries
        $result = $branches->map(function (Branch $branch) use ($allLogs, $employeesWithIssues, $branchPoints) {
            $logs = $allLogs->get($branch->id, collect());
            $totalLogs = $logs->count();
            $lateLogs = $logs->where('status', 'late')->count();
            $absentLogs = $logs->where('status', 'absent')->count();
            $financialLoss = round($logs->sum('delay_cost'), 2);
            $issueCount = $employeesWithIssues->get($branch->id, 0);
            $perfectEmployees = max(0, $branch->users_count - $issueCount);
            $points = (int) $branchPoints->get($branch->id, 0);

            $score = 1000
                - ($lateLogs * 5)
                - ($absentLogs * 15)
                + ($perfectEmployees * 20)
                + (int) ($points * 0.1);

            $score = max(0, $score);

            return [
                'id'                => $branch->id,
                'name'              => $branch->name_ar,
                'code'              => $branch->code,
                'employees'         => $branch->users_count,
                'late_checkins'     => $lateLogs,
                'missed_days'       => $absentLogs,
                'financial_loss'    => $financialLoss,
                'perfect_employees' => $perfectEmployees,
                'total_points'      => $points,
                'score'             => $score,
                'level'             => $this->calculateLevel($score),
            ];
        })
        ->sortByDesc('score')
        ->values();

        $this->totalBranches = $result->count();

        // Find user's branch rank
        if ($user->branch_id) {
            $rank = 1;
            foreach ($result as $b) {
                if ($b['id'] === $user->branch_id) {
                    $this->myBranchRank = $rank;
                    $this->myBranch = $b;
                    $this->myBranchLevel = $b['level'];
                    break;
                }
                $rank++;
            }
        }

        // Top 3 branches
        $this->topBranches = $result->take(3)->toArray();
    }

    private function calculateLevel(int $score): string
    {
        return match (true) {
            $score >= 950  => 'legendary',
            $score >= 850  => 'diamond',
            $score >= 700  => 'gold',
            $score >= 500  => 'silver',
            $score >= 300  => 'bronze',
            default        => 'starter',
        };
    }

    public function render()
    {
        return view('livewire.competition-widget');
    }
}
