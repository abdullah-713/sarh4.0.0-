<div class="bg-white rounded-xl p-5" style="border: 1px solid #E6E9ED;">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: #EBF7FE;">
            <svg class="w-4 h-4" style="color: #2AABEE;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <span class="text-sm font-bold text-gray-900">{{ __('pwa.competition_title') }}</span>
    </div>

    {{-- My Branch --}}
    @if($myBranch)
    <div class="mb-4 p-3 rounded-lg" style="border: 1.5px solid {{ match($myBranchLevel) {
        'legendary' => '#7B68EE',
        'diamond'   => '#4FC3F7',
        'gold'      => '#FFD54F',
        'silver'    => '#B0BEC5',
        'bronze'    => '#FF9800',
        default     => '#E6E9ED',
    } }}; background: {{ match($myBranchLevel) {
        'legendary' => '#F3F0FF',
        'diamond'   => '#E1F5FE',
        'gold'      => '#FFFDE7',
        'silver'    => '#F5F5F5',
        'bronze'    => '#FFF3E0',
        default     => '#F7F8FA',
    } }};">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold" style="color: {{ match($myBranchLevel) {
                    'legendary' => '#7B68EE',
                    'diamond'   => '#039BE5',
                    'gold'      => '#F9A825',
                    'silver'    => '#78909C',
                    'bronze'    => '#E65100',
                    default     => '#707579',
                } }};">#{{ $myBranchRank }}</span>
                <div>
                    <div class="font-semibold text-gray-900 text-sm">{{ $myBranch['name'] }}</div>
                    <div class="text-[11px]" style="color: #707579;">{{ __('pwa.your_branch') }}</div>
                </div>
            </div>
            <div class="text-center">
                <div class="text-lg">
                    @switch($myBranchLevel)
                        @case('legendary') 👑 @break
                        @case('diamond')   💎 @break
                        @case('gold')      🥇 @break
                        @case('silver')    🥈 @break
                        @case('bronze')    🥉 @break
                        @default           🏁
                    @endswitch
                </div>
                <div class="text-[10px] font-semibold" style="color: #707579;">{{ __('competition.level_' . $myBranchLevel) }}</div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 text-center">
            <div>
                <div class="text-sm font-bold text-gray-900">{{ number_format($myBranch['score']) }}</div>
                <div class="text-[10px]" style="color: #707579;">{{ __('competition.score') }}</div>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-900">{{ $myBranch['perfect_employees'] }}</div>
                <div class="text-[10px]" style="color: #707579;">{{ __('competition.perfect_employees') }}</div>
            </div>
            <div>
                <div class="text-sm font-bold" style="color: {{ $myBranch['financial_loss'] > 0 ? '#E53935' : '#4DCD5E' }};">
                    {{ number_format($myBranch['financial_loss'], 0) }}
                </div>
                <div class="text-[10px]" style="color: #707579;">{{ __('competition.financial_loss') }} ({{ __('competition.sar') }})</div>
            </div>
        </div>
    </div>
    @endif

    {{-- Top Branches --}}
    <div>
        <h4 class="text-[11px] font-semibold uppercase mb-2" style="color: #707579;">{{ __('pwa.top_branches') }}</h4>
        <div class="space-y-1.5">
            @foreach($topBranches as $index => $branch)
                <div class="flex items-center justify-between p-2 rounded-lg"
                     style="background: {{ $myBranch && $branch['id'] === $myBranch['id'] ? '#EBF7FE' : '#F7F8FA' }};
                            {{ $myBranch && $branch['id'] === $myBranch['id'] ? 'border: 1px solid #D6EFFD;' : '' }}">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold"
                              style="background: {{ match($index) { 0 => '#FFF3E0', 1 => '#F5F5F5', 2 => '#FBE9E7', default => '#F7F8FA' } }};
                                     color: {{ match($index) { 0 => '#F9A825', 1 => '#78909C', 2 => '#E65100', default =>  '#707579' } }};">
                            {{ $index + 1 }}
                        </span>
                        <span class="text-sm font-medium text-gray-900">{{ $branch['name'] }}</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs" style="color: #707579;">
                        <span>{{ number_format($branch['score']) }} {{ __('competition.score') }}</span>
                        <span style="color: {{ $branch['financial_loss'] > 0 ? '#E53935' : '#4DCD5E' }};">
                            {{ number_format($branch['financial_loss'], 0) }} {{ __('competition.sar') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if($totalBranches === 0)
        <p class="text-sm text-center py-4" style="color: #A0A4A8;">{{ __('competition.no_branches') }}</p>
    @endif
</div>
