<x-filament-widgets::widget>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-5" style="border: 1px solid #E6E9ED;">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg" style="background: #2AABEE;">
                    {{ mb_substr(auth()->user()->name ?? '', 0, 1) }}
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ __('pwa.welcome') }}، {{ auth()->user()->name ?? '' }}</h2>
                    <p class="text-sm" style="color: #707579;">{{ now()->locale('ar')->translatedFormat('l، j F Y') }}</p>
                </div>
            </div>
            <div class="text-left">
                @if($checkedIn ?? false)
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold text-white" style="background: #4DCD5E;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('pwa.checked_in') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold" style="background: #FFF3E0; color: #E65100;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('pwa.not_checked_in') }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
