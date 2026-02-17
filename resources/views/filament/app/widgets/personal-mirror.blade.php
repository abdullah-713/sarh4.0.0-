<x-filament-widgets::widget>
    <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden" style="border: 1px solid #E6E9ED;">
        {{-- Header --}}
        <div class="p-5 text-center text-white" style="background: linear-gradient(135deg, #2AABEE 0%, #229ED9 100%);">
            <h3 class="text-sm font-medium opacity-80 mb-2">{{ __('pwa.performance_score') }}</h3>
            <div class="relative w-24 h-24 mx-auto mb-2">
                <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="3"/>
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="white" stroke-width="3" stroke-dasharray="{{ ($score ?? 0) }}, 100" stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold">{{ $score ?? 0 }}%</span>
                </div>
            </div>
            <p class="text-sm opacity-80">{{ $scoreLabel ?? __('pwa.good') }}</p>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 gap-px" style="background: #E6E9ED;">
            <div class="bg-white dark:bg-gray-800 p-4 text-center">
                <div class="text-2xl font-bold" style="color: #4DCD5E;">{{ $presentDays ?? 0 }}</div>
                <div class="text-xs mt-1" style="color: #707579;">{{ __('pwa.present_days') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 text-center">
                <div class="text-2xl font-bold" style="color: #FF9800;">{{ $lateDays ?? 0 }}</div>
                <div class="text-xs mt-1" style="color: #707579;">{{ __('pwa.late_days') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 text-center">
                <div class="text-2xl font-bold" style="color: #E53935;">{{ $totalLoss ?? ('0 ' . __('pwa.currency')) }}</div>
                <div class="text-xs mt-1" style="color: #707579;">{{ __('pwa.total_loss') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 text-center">
                <div class="text-2xl font-bold" style="color: #2AABEE;">{{ $currentStreak ?? 0 }}</div>
                <div class="text-xs mt-1" style="color: #707579;">{{ __('pwa.current_streak') }}</div>
            </div>
        </div>

        {{-- Branch Rank --}}
        <div class="p-4 flex items-center justify-between" style="background: #F7F8FA;">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5" style="color: #FF9800;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('pwa.branch_rank') }}</span>
            </div>
            <span class="text-sm font-bold" style="color: #2AABEE;">#{{ $branchRank ?? '-' }}</span>
        </div>

        {{-- Rates --}}
        <div class="p-4 space-y-3">
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span style="color: #707579;">{{ __('pwa.on_time_rate') }}</span>
                    <span class="font-bold" style="color: #4DCD5E;">{{ $onTimeRate ?? 0 }}%</span>
                </div>
                <div class="w-full h-2 rounded-full" style="background: #E6E9ED;">
                    <div class="h-2 rounded-full" style="background: #4DCD5E; width: {{ $onTimeRate ?? 0 }}%;"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span style="color: #707579;">{{ __('pwa.attendance_rate') }}</span>
                    <span class="font-bold" style="color: #2AABEE;">{{ $attendanceRate ?? 0 }}%</span>
                </div>
                <div class="w-full h-2 rounded-full" style="background: #E6E9ED;">
                    <div class="h-2 rounded-full" style="background: #2AABEE; width: {{ $attendanceRate ?? 0 }}%;"></div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
