<div class="bg-white rounded-xl p-5" style="border: 1px solid #E6E9ED;">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: #FFF3E0;">
            <svg class="w-4 h-4" style="color: #FF9800;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
        </div>
        <span class="text-sm font-bold text-gray-900">{{ __('pwa.gamification_title') }}</span>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: #2AABEE;">{{ number_format($totalPoints) }}</div>
            <div class="text-[11px]" style="color: #707579;">{{ __('pwa.points') }}</div>
        </div>
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: #FF9800;">{{ $currentStreak }}</div>
            <div class="text-[11px]" style="color: #707579;">{{ __('pwa.current_streak') }}</div>
        </div>
        <div class="text-center p-3 rounded-lg" style="background: #F7F8FA;">
            <div class="text-xl font-bold" style="color: #7B68EE;">{{ $longestStreak }}</div>
            <div class="text-[11px]" style="color: #707579;">{{ __('pwa.best_streak') }}</div>
        </div>
    </div>

    {{-- Badges --}}
    @if(count($badges) > 0)
    <div>
        <h4 class="text-[11px] font-semibold mb-2" style="color: #707579;">{{ __('pwa.earned_badges') }}</h4>
        <div class="flex flex-wrap gap-1.5">
            @foreach($badges as $badge)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                      style="background-color: {{ $badge->color }}15; color: {{ $badge->color }};">
                    {{ $badge->name }}
                </span>
            @endforeach
        </div>
    </div>
    @else
    <p class="text-sm text-center py-2" style="color: #A0A4A8;">{{ __('pwa.no_badges') }}</p>
    @endif
</div>
