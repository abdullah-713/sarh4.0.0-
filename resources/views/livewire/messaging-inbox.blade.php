<div class="space-y-3">
    {{-- Header --}}
    <div class="flex items-center justify-between px-1">
        <h2 class="text-base font-bold text-gray-900">{{ __('pwa.messaging_title') }}</h2>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full text-white" style="background: #2AABEE;">
            {{ $this->totalUnread }}
        </span>
    </div>

    {{-- Conversations --}}
    <div class="space-y-0.5">
        @forelse($this->conversations as $conversation)
        <a href="{{ route('messaging.chat', $conversation) }}" wire:navigate
           class="block bg-white rounded-xl px-4 py-3 transition-colors hover:bg-gray-50 {{ $conversation->unread_count > 0 ? 'border-s-3' : '' }}"
           @if($conversation->unread_count > 0) style="border-right-color: #2AABEE;" @endif>
            <div class="flex items-center gap-3">
                {{-- Avatar --}}
                <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                     style="background: {{ $conversation->type === 'group' ? '#7B68EE' : '#2AABEE' }};">
                    @if($conversation->type === 'group')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    @else
                        {{ mb_substr($conversation->participants->where('id', '!=', auth()->id())->first()?->name ?? '?', 0, 1) }}
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="font-semibold text-gray-900 truncate text-sm">
                            @if($conversation->type === 'group')
                                {{ $conversation->title ?? __('pwa.msg_group') }}
                            @else
                                {{ $conversation->participants->where('id', '!=', auth()->id())->first()?->name ?? __('pwa.msg_unknown') }}
                            @endif
                        </h3>
                        @if($conversation->latestMessage)
                        <span class="text-[11px] flex-shrink-0" style="color: #707579;">
                            {{ $conversation->latestMessage->created_at->diffForHumans(short: true) }}
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between gap-2 mt-0.5">
                        <p class="text-sm truncate" style="color: #707579;">
                            {{ $conversation->latestMessage?->body ?? __('pwa.msg_no_messages') }}
                        </p>
                        @if($conversation->unread_count > 0)
                        <span class="text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: #2AABEE;">
                            {{ $conversation->unread_count }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </a>
        @empty
        <div class="bg-white rounded-xl text-center py-12 px-4">
            <svg class="w-16 h-16 mx-auto mb-3" fill="none" stroke="#C4C9CC" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p style="color: #707579;">{{ __('pwa.msg_empty') }}</p>
        </div>
        @endforelse
    </div>
</div>
