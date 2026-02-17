<div class="bg-white rounded-xl p-5" style="border: 1px solid #E6E9ED;" x-data="{
    latitude: null,
    longitude: null,
    geoError: null,
    geoLoading: true,
    watchId: null,
    init() {
        if (navigator.geolocation) {
            this.watchId = navigator.geolocation.watchPosition(
                pos => {
                    this.latitude = pos.coords.latitude;
                    this.longitude = pos.coords.longitude;
                    this.geoLoading = false;
                    $wire.updateGeolocation(this.latitude, this.longitude);
                },
                err => {
                    this.geoError = err.message;
                    this.geoLoading = false;
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
            );
        } else {
            this.geoError = '{{ __('pwa.gps_error') }}';
            this.geoLoading = false;
        }
    },
    destroy() {
        if (this.watchId) navigator.geolocation.clearWatch(this.watchId);
    }
}">
    {{-- Header --}}
    <div class="flex items-center gap-2 mb-4">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: #EBF7FE;">
            <svg class="w-4 h-4" style="color: #2AABEE;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <span class="text-sm font-bold text-gray-900">{{ __('pwa.attendance_title') }}</span>
    </div>

    {{-- GPS Status --}}
    <div class="mb-4 p-3 rounded-lg" style="border: 1px solid {{ $isInsideGeofence ? '#C8E6C9' : '#FFCDD2' }}; background: {{ $isInsideGeofence ? '#E8F5E9' : '#FFEBEE' }};">
        <template x-if="geoLoading">
            <div class="flex items-center gap-2" style="color: #707579;">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="text-sm">{{ __('pwa.gps_acquiring') }}</span>
            </div>
        </template>

        <template x-if="!geoLoading && !geoError">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        @if($isInsideGeofence)
                            <span class="w-2 h-2 rounded-full animate-pulse" style="background: #4DCD5E;"></span>
                            <span class="text-sm font-semibold" style="color: #2E7D32;">{{ __('pwa.inside_geofence') }}</span>
                        @else
                            <span class="w-2 h-2 rounded-full animate-pulse" style="background: #E53935;"></span>
                            <span class="text-sm font-semibold" style="color: #C62828;">{{ __('pwa.outside_geofence') }}</span>
                        @endif
                    </div>
                    <span class="text-xs font-mono" style="color: #707579;">
                        {{ $distanceMeters }}{{ __('pwa.meters') }} / {{ $geofenceRadius }}{{ __('pwa.meters') }}
                    </span>
                </div>
                @if($geofenceRadius > 0)
                    <div class="w-full h-1.5 rounded-full overflow-hidden" style="background: #E0E0E0;">
                        @php
                            $pct = min(100, ($distanceMeters / $geofenceRadius) * 100);
                        @endphp
                        <div class="h-full rounded-full transition-all duration-500"
                             style="width: {{ $pct }}%; background: {{ $isInsideGeofence ? '#4DCD5E' : '#E53935' }};"></div>
                    </div>
                @endif
            </div>
        </template>

        <template x-if="geoError">
            <div class="flex items-center gap-2" style="color: #E65100;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <span class="text-sm" x-text="geoError"></span>
            </div>
        </template>
    </div>

    {{-- Status --}}
    <div class="flex items-center gap-3 mb-4">
        @if($status === 'checked_in')
            <span class="badge-success">
                <span class="w-2 h-2 rounded-full me-1.5 animate-pulse" style="background: #4DCD5E;"></span>
                {{ __('pwa.status_checked_in') }}
            </span>
            <span class="text-sm" style="color: #707579;">{{ $checkInTime }}</span>
        @elseif($status === 'checked_out')
            <span class="badge-warning">{{ __('pwa.status_checked_out') }}</span>
            <span class="text-sm" style="color: #707579;">{{ $checkInTime }} → {{ $checkOutTime }}</span>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background: #F0F2F5; color: #707579;">
                {{ __('pwa.status_not_checked_in') }}
            </span>
        @endif
    </div>

    {{-- Action Buttons --}}
    <div class="flex gap-3">
        @if($status === 'not_checked_in')
            <button @click="if(latitude) $wire.checkIn(latitude, longitude)" :disabled="!latitude"
                class="btn-primary text-sm flex-1 flex items-center justify-center gap-2"
                :class="{ 'opacity-50 cursor-not-allowed': !latitude }">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                {{ __('pwa.btn_check_in') }}
            </button>
        @elseif($status === 'checked_in')
            <button @click="if(latitude) $wire.checkOut(latitude, longitude)" :disabled="!latitude"
                class="btn-secondary text-sm flex-1 flex items-center justify-center gap-2"
                :class="{ 'opacity-50 cursor-not-allowed': !latitude }">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                {{ __('pwa.btn_check_out') }}
            </button>
        @else
            <div class="w-full text-center py-2 text-sm rounded-lg" style="background: #E8F5E9; color: #2E7D32;">
                {{ __('pwa.btn_done') }} ✓
            </div>
        @endif
    </div>

    {{-- Messages --}}
    @if($message)
        <div class="mt-3 text-sm rounded-lg px-3 py-2"
             style="{{ $messageType === 'success' ? 'background: #E8F5E9; color: #2E7D32;' : 'background: #FFEBEE; color: #C62828;' }}">
            {{ $message }}
        </div>
    @endif

    {{-- Separator --}}
    <hr class="my-4" style="border-color: #E6E9ED;">

    {{-- Whistleblower Button --}}
    <div>
        <button wire:click="toggleWhistleblowerForm"
            class="w-full flex items-center justify-center gap-2 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200"
            style="{{ $showWhistleblowerForm ? 'background: #E53935; color: white;' : 'background: #F0F2F5; color: #707579;' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            {{ __('pwa.wb_title') }}
        </button>

        @if($showWhistleblowerForm)
            <div class="mt-3 p-4 rounded-xl" style="border: 1px solid #FFCDD2; background: #FFF5F5;">
                @if($wbTicket)
                    <div class="text-center space-y-3">
                        <div class="w-12 h-12 mx-auto rounded-full flex items-center justify-center" style="background: #E8F5E9;">
                            <svg class="w-6 h-6" style="color: #4DCD5E;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h4 class="font-bold" style="color: #2E7D32;">{{ __('pwa.wb_success_title') }}</h4>
                        <div class="bg-white rounded-lg p-3 space-y-2">
                            <div class="text-xs" style="color: #707579;">{{ __('pwa.wb_ticket') }}</div>
                            <div class="font-mono font-bold text-lg">{{ $wbTicket }}</div>
                        </div>
                        <div class="rounded-lg p-3 space-y-2" style="background: #FFF3E0; border: 1px solid #FFE0B2;">
                            <div class="text-xs font-semibold" style="color: #E65100;">{{ __('pwa.wb_secret_token') }}</div>
                            <div class="font-mono text-sm break-all select-all" style="color: #BF360C;">{{ $wbToken }}</div>
                            <div class="text-xs" style="color: #E65100;">{{ __('pwa.wb_token_warning') }}</div>
                        </div>
                        <button wire:click="toggleWhistleblowerForm" class="btn-primary text-sm">{{ __('pwa.wb_new_report') }}</button>
                    </div>
                @else
                    <div class="mb-3 p-2 rounded-lg text-xs space-y-1" style="background: white; color: #707579;">
                        <div class="font-semibold mb-1" style="color: #C62828;">{{ __('pwa.wb_security_title') }}</div>
                        <div>🔒 {{ __('pwa.wb_security_1') }}</div>
                        <div>🔐 {{ __('pwa.wb_security_2') }}</div>
                        <div>🎫 {{ __('pwa.wb_security_3') }}</div>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('pwa.wb_category') }}</label>
                            <select wire:model="wbCategory" class="input-field text-sm">
                                <option value="">{{ __('pwa.wb_select_category') }}</option>
                                <option value="fraud">{{ __('pwa.wb_cat_fraud') }}</option>
                                <option value="corruption">{{ __('pwa.wb_cat_corruption') }}</option>
                                <option value="harassment">{{ __('pwa.wb_cat_harassment') }}</option>
                                <option value="safety">{{ __('pwa.wb_cat_safety') }}</option>
                                <option value="discrimination">{{ __('pwa.wb_cat_discrimination') }}</option>
                                <option value="other">{{ __('pwa.wb_cat_other') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('pwa.wb_severity') }}</label>
                            <select wire:model="wbSeverity" class="input-field text-sm">
                                <option value="low">{{ __('pwa.wb_sev_low') }}</option>
                                <option value="medium">{{ __('pwa.wb_sev_medium') }}</option>
                                <option value="high">{{ __('pwa.wb_sev_high') }}</option>
                                <option value="critical">{{ __('pwa.wb_sev_critical') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('pwa.wb_content') }}</label>
                            <textarea wire:model="wbContent" rows="4" class="input-field text-sm" placeholder="{{ __('pwa.wb_content_placeholder') }}"></textarea>
                        </div>
                        <button wire:click="submitWhistleblowerReport"
                            class="w-full py-2.5 rounded-lg text-white text-sm font-semibold transition-colors"
                            style="background: #E53935;">
                            {{ __('pwa.wb_submit') }}
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
