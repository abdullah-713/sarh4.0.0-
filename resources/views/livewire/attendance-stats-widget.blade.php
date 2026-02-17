<div class="bg-white rounded-xl p-5" style="border: 1px solid #E6E9ED;">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: #E8F5E9;">
            <svg class="w-4 h-4" style="color: #4DCD5E;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <span class="text-sm font-bold text-gray-900">{{ __('pwa.attendance_stats_title') }}</span>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-4 gap-2 mb-4">
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: #4DCD5E;">{{ $presentDays }}</div>
            <div class="text-[11px]" style="color: #707579;">{{ __('pwa.present') }}</div>
        </div>
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: #FF9800;">{{ $lateDays }}</div>
            <div class="text-[11px]" style="color: #707579;">{{ __('pwa.late') }}</div>
        </div>
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: #E53935;">{{ $absentDays }}</div>
            <div class="text-[11px]" style="color: #707579;">{{ __('pwa.absent') }}</div>
        </div>
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: #2AABEE;">{{ $onTimeStreak }}</div>
            <div class="text-[11px]" style="color: #707579;">{{ __('pwa.streak') }}</div>
        </div>
    </div>

    {{-- Extra Metrics --}}
    <div class="grid grid-cols-3 gap-2 mb-4">
        <div class="text-center p-2 rounded-lg" style="background: #F7F8FA;">
            <div class="text-sm font-bold text-gray-800">{{ $avgDelayMinutes > 0 ? number_format($avgDelayMinutes, 1) : '0' }}</div>
            <div class="text-[10px]" style="color: #707579;">{{ __('pwa.avg_delay') }} ({{ __('attendance.min') }})</div>
        </div>
        <div class="text-center p-2 rounded-lg" style="background: #F7F8FA;">
            <div class="text-sm font-bold text-gray-800">{{ $totalWorkedMinutes > 0 ? number_format($totalWorkedMinutes / 60, 1) : '0' }}</div>
            <div class="text-[10px]" style="color: #707579;">{{ __('pwa.hours_worked') }}</div>
        </div>
        <div class="text-center p-2 rounded-lg" style="background: #F7F8FA;">
            <div class="text-sm font-bold" style="color: #4DCD5E;">{{ $totalOvertimeMinutes > 0 ? number_format($totalOvertimeMinutes / 60, 1) : '0' }}</div>
            <div class="text-[10px]" style="color: #707579;">{{ __('pwa.overtime_hours') }}</div>
        </div>
    </div>

    {{-- Weekly Breakdown --}}
    @if(count($weeklyBreakdown) > 0)
    <div>
        <h4 class="text-[11px] font-semibold uppercase mb-2" style="color: #707579;">{{ __('pwa.weekly_breakdown') }}</h4>
        <div class="space-y-2">
            @foreach($weeklyBreakdown as $week)
                <div class="flex items-center gap-3">
                    <span class="text-xs w-14 shrink-0" style="color: #707579;">{{ $week['label'] }}</span>
                    <div class="flex-1">
                        <div class="w-full rounded-full h-1.5" style="background: #E6E9ED;">
                            <div class="h-1.5 rounded-full transition-all duration-500"
                                 style="width: {{ $week['rate'] }}%; background: {{ $week['rate'] >= 90 ? '#4DCD5E' : ($week['rate'] >= 70 ? '#FF9800' : '#E53935') }};">
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 text-xs shrink-0">
                        <span class="font-medium" style="color: #4DCD5E;">{{ $week['on_time'] }}</span>
                        @if($week['late'] > 0)
                            <span style="color: #FF9800;">/ {{ $week['late'] }}</span>
                        @endif
                        @if($week['absent'] > 0)
                            <span style="color: #E53935;">/ {{ $week['absent'] }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-end gap-3 mt-1.5 text-[10px]" style="color: #A0A4A8;">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full" style="background: #4DCD5E;"></span>{{ __('pwa.present') }}</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full" style="background: #FF9800;"></span>{{ __('pwa.late') }}</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full" style="background: #E53935;"></span>{{ __('pwa.absent') }}</span>
        </div>
    </div>
    @else
        <p class="text-sm text-center py-2" style="color: #A0A4A8;">{{ __('pwa.no_attendance_data') }}</p>
    @endif
</div>
