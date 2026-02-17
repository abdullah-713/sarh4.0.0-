<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Orange Gradient Banner --}}
        <div class="rounded-2xl bg-gradient-to-r from-orange-500 to-amber-400 p-6 text-center text-white shadow-lg">
            <h2 class="text-3xl font-bold">{{ __('competition.leaderboard_title') }}</h2>
            <p class="mt-1 opacity-90">{{ __('competition.leaderboard_subtitle') }}</p>
            <p class="mt-1 text-sm opacity-75">
                {{ __('competition.ranking_method') }}: {{ __('competition.ranking_by_loss') }}
            </p>
            <p class="text-xs opacity-60 mt-1">
                {{ __('competition.period') }}: {{ now()->startOfMonth()->format('Y-m-d') }} &rarr; {{ now()->format('Y-m-d') }}
            </p>
        </div>

        @php $branches = $this->getBranches(); @endphp

        @if(count($branches) === 0)
            <div class="text-center py-12 text-gray-400">
                <x-heroicon-o-trophy class="w-16 h-16 mx-auto mb-4 opacity-25" />
                <p>{{ __('competition.no_branches') }}</p>
            </div>
        @else
            <div class="grid gap-4">
                @foreach($branches as $item)
                    <div class="rounded-2xl border-2 p-6 transition-all hover:shadow-lg {{ $item['level']['bg'] }}">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            {{-- Rank + Name --}}
                            <div class="flex items-center gap-4">
                                <div class="text-4xl font-black {{ $item['level']['color'] }}">
                                    #{{ $item['rank'] }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-2xl">{{ $item['level']['icon'] }}</span>
                                        <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                                            {{ $item['branch']->name_ar }}
                                        </h3>
                                        @if($item['badge'])
                                            <span class="text-2xl animate-bounce">{{ $item['badge'] }}</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500">
                                        {{ $item['branch']->name_en }} &mdash; {{ $item['branch']->city }}
                                    </p>
                                    <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold {{ $item['level']['color'] }} {{ $item['level']['bg'] }}">
                                        {{ $item['level']['name'] }}
                                    </span>
                                    @if($item['badge_label'])
                                        <span class="inline-block mt-1 ms-2 px-3 py-1 rounded-full text-xs font-bold bg-white/60">
                                            {{ $item['badge_label'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Financial Loss (primary metric) --}}
                            <div class="text-center">
                                <div class="text-4xl font-black {{ $item['total_loss'] == 0 ? 'text-green-600' : $item['level']['color'] }}">
                                    {{ number_format($item['total_loss'], 0) }}
                                    <span class="text-sm">{{ __('competition.sar') }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ __('competition.financial_loss') }}
                                </div>
                            </div>
                        </div>

                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mt-4 pt-4 border-t border-gray-200/50">
                            <div class="text-center">
                                <div class="text-lg font-bold text-gray-700 dark:text-gray-200">{{ $item['employee_count'] }}</div>
                                <div class="text-xs text-gray-500">{{ __('competition.employees') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-red-500">{{ $item['late_checkins'] }}</div>
                                <div class="text-xs text-gray-500">{{ __('competition.late_checkins') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-orange-600">{{ number_format($item['total_delay']) }} {{ __('competition.min') }}</div>
                                <div class="text-xs text-gray-500">{{ __('competition.total_delay') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-green-600">{{ $item['perfect_employees'] }}</div>
                                <div class="text-xs text-gray-500">{{ __('competition.perfect_employees') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-amber-600">{{ $item['total_points'] }}</div>
                                <div class="text-xs text-gray-500">{{ __('competition.total_points') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Scoring Legend --}}
            <div class="mt-8 rounded-xl bg-gray-50 dark:bg-gray-800 p-6 border">
                <h4 class="font-bold text-gray-700 dark:text-gray-200 mb-3">{{ __('competition.scoring_legend') }}</h4>
                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                    <p>{{ __('competition.ranking_method') }}: <strong>{{ __('competition.ranking_by_loss') }}</strong></p>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
