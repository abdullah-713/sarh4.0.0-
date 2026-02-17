{{-- Branch Progress Hero Card (Telegram Style) --}}
<div class="overflow-hidden rounded-xl" style="border: 1px solid #E6E9ED;">
    {{-- Gradient Hero --}}
    <div class="relative overflow-hidden px-5 py-5" style="background: linear-gradient(135deg, #2AABEE, #229ED9, #1C96CC);">
        <div class="absolute top-0 right-0 w-32 h-32 rounded-full" style="background: rgba(255,255,255,0.08); transform: translate(30%, -30%);"></div>

        @if($branchName)
        <div class="relative z-10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color: rgba(255,255,255,0.8);">📊 إحصائيات الأسبوع · {{ $periodLabel }}</p>
                    <h2 class="text-xl font-bold text-white mt-1">{{ $branchName }}</h2>
                    <p class="text-sm mt-0.5" style="color: rgba(255,255,255,0.7);">{{ $branchEmployees }} {{ __('competition.employees') }}</p>
                </div>
                <div class="text-center">
                    <div class="text-4xl">
                        @switch($currentLevel)
                            @case('legendary') 👑 @break
                            @case('diamond')   💎 @break
                            @case('gold')      🥇 @break
                            @case('silver')    🥈 @break
                            @case('bronze')    🥉 @break
                            @default           🏁
                        @endswitch
                    </div>
                    <div class="text-xs font-bold text-white mt-0.5">{{ __('competition.level_' . $currentLevel) }}</div>
                </div>
            </div>

            {{-- Score & Progress --}}
            <div class="mt-4">
                <div class="flex justify-between items-center mb-1.5">
                    <span class="text-white font-bold">{{ number_format($currentScore) }} نقطة</span>
                    @if($nextLevel)
                        <span class="text-sm" style="color: rgba(255,255,255,0.7);">{{ __('competition.level_' . $nextLevel) }} — {{ number_format($nextLevelThreshold) }}</span>
                    @else
                        <span class="text-white font-bold text-sm">🏆 {{ __('pwa.max_level') }}</span>
                    @endif
                </div>
                <div class="h-2 rounded-full overflow-hidden" style="background: rgba(255,255,255,0.2);">
                    <div class="h-full rounded-full transition-all duration-700" style="width: {{ $progressPercent }}%; background: white;"></div>
                </div>
            </div>
        </div>
        @else
        <div class="relative z-10 text-center py-3">
            <p class="text-sm" style="color: rgba(255,255,255,0.8);">{{ __('pwa.no_branch_assigned') }}</p>
        </div>
        @endif
    </div>

    {{-- Stats Grid --}}
    @if($branchName)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 bg-white">
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: {{ $attendanceRate >= 90 ? '#4DCD5E' : ($attendanceRate >= 70 ? '#FF9800' : '#E53935') }};">{{ $attendanceRate }}%</div>
            <div class="text-[10px] mt-0.5 font-medium" style="color: #707579;">{{ __('pwa.branch_attendance_rate') }}</div>
        </div>
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: {{ $branchDelayCost > 0 ? '#E53935' : '#4DCD5E' }};">{{ number_format($branchDelayCost, 0) }}</div>
            <div class="text-[10px] mt-0.5 font-medium" style="color: #707579;">{{ __('competition.financial_loss') }}</div>
        </div>
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: #2AABEE;">{{ $perfectEmployees }}</div>
            <div class="text-[10px] mt-0.5 font-medium" style="color: #707579;">{{ __('competition.perfect_employees') }}</div>
        </div>
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: #FF9800;">{{ $lateCount }}</div>
            <div class="text-[10px] mt-0.5 font-medium" style="color: #707579;">{{ __('competition.late_checkins') }}</div>
        </div>
    </div>
    @endif
</div>
