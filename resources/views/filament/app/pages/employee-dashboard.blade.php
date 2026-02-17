<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Personal Mirror --}}
        @livewire(\App\Filament\App\Widgets\PersonalMirrorWidget::class)
        
        {{-- Branch Progress --}}
        @livewire(\App\Livewire\BranchProgressWidget::class)
        
        {{-- Quick Attendance --}}
        @livewire(\App\Livewire\AttendanceWidget::class)
        
        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @livewire(\App\Livewire\AttendanceStatsWidget::class)
            @livewire(\App\Livewire\FinancialWidget::class)
        </div>

        {{-- Gamification --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @livewire(\App\Livewire\GamificationWidget::class)
            @livewire(\App\Livewire\CompetitionWidget::class)
        </div>

        {{-- Circulars --}}
        @livewire(\App\Livewire\CircularsWidget::class)
    </div>
</x-filament-panels::page>
