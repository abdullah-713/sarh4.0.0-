<x-filament-panels::page>
    <div class="max-w-2xl mx-auto space-y-4">
        @if(!$submitted)
            {{-- Security Notice --}}
            <div class="p-4 rounded-xl text-sm" style="background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9;">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <div>
                        <p class="font-bold">{{ __('pwa.wb_security_title') }}</p>
                        <p class="mt-0.5">{{ __('pwa.wb_security_body') }}</p>
                    </div>
                </div>
            </div>

            {{-- Error message --}}
            @if($errorMessage)
                <div class="p-4 rounded-xl text-sm" style="background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2;">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <p>{{ $errorMessage }}</p>
                    </div>
                </div>
            @endif

            {{-- Form --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm" style="border: 1px solid var(--tg-border, #E6E9ED);">
                <form wire:submit="submit" class="space-y-5">
                    {{-- Category --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('pwa.wb_category') }}</label>
                        <select wire:model="category"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm py-2.5 px-3"
                                required>
                            <option value="">{{ __('pwa.wb_select_category') }}</option>
                            <option value="fraud">{{ __('pwa.wb_cat_fraud') }}</option>
                            <option value="harassment">{{ __('pwa.wb_cat_harassment') }}</option>
                            <option value="corruption">{{ __('pwa.wb_cat_corruption') }}</option>
                            <option value="safety">{{ __('pwa.wb_cat_safety') }}</option>
                        </select>
                        @error('category') <span class="block text-xs mt-1 text-red-600">{{ $message }}</span> @enderror
                    </div>

                    {{-- Severity --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('pwa.wb_severity') }}</label>
                        <select wire:model="severity"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm py-2.5 px-3">
                            <option value="low">{{ __('pwa.wb_sev_low') }}</option>
                            <option value="medium">{{ __('pwa.wb_sev_medium') }}</option>
                            <option value="high">{{ __('pwa.wb_sev_high') }}</option>
                            <option value="critical">{{ __('pwa.wb_sev_critical') }}</option>
                        </select>
                        @error('severity') <span class="block text-xs mt-1 text-red-600">{{ $message }}</span> @enderror
                    </div>

                    {{-- Content --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">{{ __('pwa.wb_content') }}</label>
                        <textarea wire:model="content"
                                  rows="5"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm py-2.5 px-3"
                                  placeholder="{{ __('pwa.wb_content_placeholder') }}"
                                  required
                                  minlength="20"></textarea>
                        @error('content') <span class="block text-xs mt-1 text-red-600">{{ $message }}</span> @enderror
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full py-3 rounded-lg text-white text-sm font-bold transition-all duration-200 hover:opacity-90 active:scale-[0.98]"
                            style="background: #E53935;"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60 cursor-wait">
                        <span wire:loading.remove wire:target="submit">
                            <svg class="w-4 h-4 inline-block me-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            {{ __('pwa.wb_submit') }}
                        </span>
                        <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ __('pwa.loading') }}...
                        </span>
                    </button>
                </form>
            </div>

        @else
            {{-- Success --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 text-center space-y-5 shadow-sm" style="border: 1px solid var(--tg-border, #E6E9ED);">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto" style="background: #E8F5E9;">
                    <svg class="w-8 h-8" style="color: #4DCD5E;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">{{ __('pwa.wb_success_title') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('pwa.wb_success_body') }}</p>
                </div>

                {{-- Ticket number --}}
                <div class="p-4 rounded-xl" style="background: #F7F8FA;">
                    <p class="text-sm text-gray-500 mb-1">{{ __('pwa.wb_ticket') }}</p>
                    <p class="text-xl font-bold font-mono text-gray-900 dark:text-gray-100 select-all">{{ $ticketNumber }}</p>
                </div>

                {{-- Secret token --}}
                <div class="p-4 rounded-xl" style="background: #FFF3E0; border: 1px solid #FFE0B2;">
                    <p class="text-sm font-bold mb-1.5" style="color: #E65100;">
                        <svg class="w-4 h-4 inline-block me-1 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        {{ __('pwa.wb_secret_token') }}
                    </p>
                    <p class="text-xs font-mono break-all select-all p-2 rounded bg-white/50" style="color: #BF360C;">{{ $anonymousToken }}</p>
                    <p class="text-xs mt-2" style="color: #E65100;">{{ __('pwa.wb_token_warning') }}</p>
                </div>

                <button wire:click="resetForm"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold transition-all hover:opacity-90"
                        style="background: #E7EBF0; color: #3E546A;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('pwa.wb_new_report') }}
                </button>
            </div>
        @endif
    </div>
</x-filament-panels::page>
