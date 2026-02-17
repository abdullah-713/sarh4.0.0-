<div class="max-w-2xl mx-auto space-y-4">
    <div class="bg-white rounded-xl p-5" style="border: 1px solid #E6E9ED;">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: #EBF7FE;">
                <svg class="w-5 h-5" style="color: #2AABEE;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-900">{{ __('pwa.wb_track_title') }}</h2>
                <p class="text-sm" style="color: #707579;">{{ __('pwa.wb_track_subtitle') }}</p>
            </div>
        </div>

        <form wire:submit="track" class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('pwa.wb_enter_token') }}</label>
                <input type="text" wire:model="token" class="input-field font-mono" placeholder="{{ __('pwa.wb_token_placeholder') }}">
                @error('token') <span class="text-xs mt-1" style="color: #E53935;">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="btn-primary w-full">
                <span wire:loading.remove>{{ __('pwa.wb_track_btn') }}</span>
                <span wire:loading class="animate-pulse">{{ __('pwa.loading') }}...</span>
            </button>
        </form>
    </div>

    @if($errorMessage)
    <div class="p-3 rounded-lg text-sm" style="background: #FFEBEE; color: #C62828;">{{ $errorMessage }}</div>
    @endif

    @if($report)
    <div class="bg-white rounded-xl p-5 space-y-4" style="border: 1px solid #E6E9ED;">
        <h3 class="text-base font-bold text-gray-900">{{ __('pwa.wb_report_status') }}</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs" style="color: #707579;">{{ __('pwa.wb_ticket') }}</p>
                <p class="font-bold font-mono">{{ $report['ticket_number'] }}</p>
            </div>
            <div>
                <p class="text-xs" style="color: #707579;">{{ __('pwa.wb_status') }}</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    style="background: {{ $report['status'] === 'resolved' ? '#E8F5E9' : ($report['status'] === 'investigating' ? '#FFF3E0' : '#EBF7FE') }};
                           color: {{ $report['status'] === 'resolved' ? '#2E7D32' : ($report['status'] === 'investigating' ? '#E65100' : '#2AABEE') }};">
                    {{ __('pwa.wb_status_' . $report['status']) }}
                </span>
            </div>
            <div>
                <p class="text-xs" style="color: #707579;">{{ __('pwa.wb_category') }}</p>
                <p class="font-medium text-sm">{{ __('pwa.wb_cat_' . $report['category']) }}</p>
            </div>
            <div>
                <p class="text-xs" style="color: #707579;">{{ __('pwa.wb_severity') }}</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    style="background: {{ $report['severity'] === 'critical' ? '#FFEBEE' : ($report['severity'] === 'high' ? '#FFF3E0' : '#F7F8FA') }};
                           color: {{ $report['severity'] === 'critical' ? '#C62828' : ($report['severity'] === 'high' ? '#E65100' : '#707579') }};">
                    {{ __('pwa.wb_sev_' . $report['severity']) }}
                </span>
            </div>
            <div>
                <p class="text-xs" style="color: #707579;">{{ __('pwa.wb_submitted_at') }}</p>
                <p class="font-medium text-sm">{{ $report['created_at'] }}</p>
            </div>
            @if($report['resolved_at'])
            <div>
                <p class="text-xs" style="color: #707579;">{{ __('pwa.wb_resolved_at') }}</p>
                <p class="font-medium text-sm">{{ $report['resolved_at'] }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="text-center">
        <a href="{{ route('whistleblower.form') }}" class="text-sm font-medium" style="color: #2AABEE;">
            {{ __('pwa.wb_new_report') }}
        </a>
    </div>
</div>
