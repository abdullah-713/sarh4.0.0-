<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('pwa.app_title') }} — {{ $title ?? __('pwa.dashboard') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="antialiased min-h-screen" style="background: #E7EBF0; font-family: 'Cairo', sans-serif;">
    <div class="flex min-h-screen" x-data="{ sidebarOpen: false }">

        {{-- Mobile Overlay --}}
        <div x-show="sidebarOpen"
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/30 lg:hidden"
             @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : (document.dir === 'rtl' ? 'translate-x-full' : '-translate-x-full')"
               class="fixed inset-y-0 z-50 w-72 bg-white transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto"
               :style="document.dir === 'rtl' ? 'right: 0' : 'left: 0'"
               style="border-left: 1px solid #E6E9ED;">

            {{-- Logo Area --}}
            <div class="flex items-center gap-3 h-14 px-5" style="border-bottom: 1px solid #E6E9ED;">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #2AABEE;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-gray-900">{{ __('pwa.app_name') }}</h1>
                    <p class="text-[10px]" style="color: #707579;">{{ __('pwa.app_subtitle') }}</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="p-3 space-y-0.5">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                   @if(request()->routeIs('dashboard')) style="background: #2AABEE;" @endif>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    {{ __('pwa.nav_dashboard') }}
                </a>

                <a href="{{ route('messaging.inbox') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('messaging.*') ? 'text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                   @if(request()->routeIs('messaging.*')) style="background: #2AABEE;" @endif>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    {{ __('pwa.nav_messages') }}
                </a>

                <a href="{{ route('whistleblower.form') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('whistleblower.*') ? 'text-white' : 'text-gray-700 hover:bg-gray-50' }}"
                   @if(request()->routeIs('whistleblower.*')) style="background: #2AABEE;" @endif>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    {{ __('pwa.nav_whistleblower') }}
                </a>
            </nav>

            {{-- User Info --}}
            @auth
            <div class="absolute bottom-0 w-full p-3" style="border-top: 1px solid #E6E9ED; background: #FFFFFF;">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm" style="background: #2AABEE;">
                        {{ mb_substr(auth()->user()->name_ar ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name_ar }}</p>
                        <p class="text-xs truncate" style="color: #707579;">{{ auth()->user()->job_title_ar ?? auth()->user()->email }}</p>
                    </div>
                </div>
            </div>
            @endauth
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top Bar --}}
            <header class="sticky top-0 z-30 bg-white h-14 flex items-center px-5 gap-4" style="border-bottom: 1px solid #E6E9ED;">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden" style="color: #707579;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h2 class="text-base font-bold text-gray-900">{{ $title ?? __('pwa.dashboard') }}</h2>
                <div class="flex-1"></div>
                <a href="?lang={{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}" class="text-sm font-medium" style="color: #2AABEE;">
                    {{ app()->getLocale() === 'ar' ? 'EN' : 'عربي' }}
                </a>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-5">
                {{ $slot }}
            </main>
        </div>
    </div>
    @livewireScripts
</body>
</html>
