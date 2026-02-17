<x-filament-panels::page.simple>
    <div class="text-center mb-6">
        <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3" style="background: #2AABEE;">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ __('pwa.app_name') }}</h1>
        <p class="text-sm mt-1" style="color: #707579;">{{ __('pwa.login_subtitle') }}</p>
    </div>

    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}
            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

    <div class="text-center mt-6">
        <p class="text-xs" style="color: #707579;">© {{ date('Y') }} {{ __('pwa.app_name') }}. {{ __('pwa.all_rights_reserved') }}</p>
    </div>
</x-filament-panels::page.simple>
