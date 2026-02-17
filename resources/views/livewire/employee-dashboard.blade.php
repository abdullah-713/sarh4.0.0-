<div class="space-y-5">
    {{-- Welcome Header --}}
    <div class="bg-white rounded-xl p-5" style="border: 1px solid #E6E9ED;">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg" style="background: #2AABEE;">
                {{ mb_substr(auth()->user()->name_ar ?? 'U', 0, 1) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ __('pwa.welcome') }}، {{ auth()->user()->name_ar }}</h2>
                <p class="text-sm" style="color: #707579;">{{ now()->translatedFormat('l، d F Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Widgets Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <livewire:attendance-widget />
        <livewire:attendance-stats-widget />
        <livewire:gamification-widget />
        <livewire:competition-widget />
        <livewire:branch-progress-widget />
        <livewire:financial-widget />
        <livewire:circulars-widget />
    </div>
</div>
