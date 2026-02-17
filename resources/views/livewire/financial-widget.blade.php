<div class="bg-white rounded-xl p-5" style="border: 1px solid #E6E9ED;">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: #EBF7FE;">
            <svg class="w-4 h-4" style="color: #2AABEE;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <span class="text-sm font-bold text-gray-900">{{ __('pwa.financial_title') }}</span>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: {{ $onTimeRate >= 90 ? '#4DCD5E' : ($onTimeRate >= 70 ? '#FF9800' : '#E53935') }};">{{ $onTimeRate }}%</div>
            <div class="text-[11px]" style="color: #707579;">{{ __('pwa.on_time_rate') }}</div>
        </div>
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: {{ $totalDelayCost > 0 ? '#E53935' : '#4DCD5E' }};">{{ number_format($totalDelayCost, 2) }}</div>
            <div class="text-[11px]" style="color: #707579;">{{ __('pwa.delay_cost') }} ({{ __('pwa.currency') }})</div>
        </div>
    </div>

    {{-- Progress --}}
    <div>
        <div class="flex justify-between text-xs mb-1" style="color: #707579;">
            <span>{{ __('pwa.this_month') }}</span>
            <span>{{ $totalDays - $lateDays }}/{{ $totalDays }} {{ __('pwa.on_time_days') }}</span>
        </div>
        <div class="w-full h-1.5 rounded-full" style="background: #E6E9ED;">
            <div class="h-1.5 rounded-full transition-all duration-500"
                 style="width: {{ $onTimeRate }}%; background: {{ $onTimeRate >= 90 ? '#4DCD5E' : ($onTimeRate >= 70 ? '#FF9800' : '#E53935') }};"></div>
        </div>
    </div>
</div>
