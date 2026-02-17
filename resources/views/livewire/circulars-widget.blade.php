<div class="bg-white rounded-xl p-5" style="border: 1px solid #E6E9ED;">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: #F3E5F5;">
            <svg class="w-4 h-4" style="color: #7B68EE;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
        </div>
        <span class="text-sm font-bold text-gray-900">{{ __('pwa.circulars_title') }}</span>
    </div>

    @if(count($circulars) > 0)
        <div class="space-y-2">
            @foreach($circulars as $circular)
                <div class="p-3 rounded-lg" style="border: 1px solid {{ $circular['acknowledged'] ? '#E6E9ED' : '#D6EFFD' }};
                     background: {{ $circular['acknowledged'] ? '#F7F8FA' : '#EBF7FE' }};">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                @if($circular['priority'] === 'urgent')
                                    <span class="badge-danger text-[10px]">{{ __('pwa.urgent') }}</span>
                                @elseif($circular['priority'] === 'important')
                                    <span class="badge-warning text-[10px]">{{ __('pwa.important') }}</span>
                                @endif
                                <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $circular['title'] }}</h4>
                            </div>
                            <p class="text-xs line-clamp-2" style="color: #707579;">{!! nl2br(e(Str::limit($circular['body'], 120))) !!}</p>
                            <p class="text-[10px] mt-1" style="color: #A0A4A8;">{{ $circular['published_at'] }}</p>
                        </div>

                        @if($circular['requires_ack'] && !$circular['acknowledged'])
                            <button wire:click="acknowledge({{ $circular['id'] }})"
                                    class="btn-primary text-xs whitespace-nowrap !px-3 !py-1.5">
                                {{ __('pwa.acknowledge') }}
                            </button>
                        @elseif($circular['acknowledged'])
                            <span class="text-xs whitespace-nowrap" style="color: #4DCD5E;">✓ {{ __('pwa.acknowledged') }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-center py-4" style="color: #A0A4A8;">{{ __('pwa.no_circulars') }}</p>
    @endif
</div>
