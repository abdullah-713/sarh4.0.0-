<x-filament-panels::page>
    <div class="space-y-6">
        {{-- رأس الصفحة مع الإحصائيات --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $overdue = \App\Models\EmployeeReminder::overdue()->count();
                $urgent = \App\Models\EmployeeReminder::urgent()->count();
                $soon = \App\Models\EmployeeReminder::expiringSoon(30)->count();
                $total = \App\Models\EmployeeReminder::where('is_completed', false)->count();
            @endphp

            <x-filament::section class="bg-gradient-to-br from-danger-50 to-danger-100 dark:from-danger-900/20 dark:to-danger-900/30 border-2 border-danger-300 dark:border-danger-700">
                <div class="text-center">
                    <div class="text-4xl font-bold text-danger-600 dark:text-danger-400 animate-pulse">
                        {{ $overdue }}
                    </div>
                    <div class="mt-2 text-sm font-medium text-danger-700 dark:text-danger-300">
                        🔴 متأخر
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-gradient-to-br from-warning-50 to-warning-100 dark:from-warning-900/20 dark:to-warning-900/30 border-2 border-warning-300 dark:border-warning-700">
                <div class="text-center">
                    <div class="text-4xl font-bold text-warning-600 dark:text-warning-400">
                        {{ $urgent }}
                    </div>
                    <div class="mt-2 text-sm font-medium text-warning-700 dark:text-warning-300">
                        🟠 عاجل (≤10 أيام)
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-900/30 border-2 border-yellow-300 dark:border-yellow-700">
                <div class="text-center">
                    <div class="text-4xl font-bold text-yellow-600 dark:text-yellow-400">
                        {{ $soon }}
                    </div>
                    <div class="mt-2 text-sm font-medium text-yellow-700 dark:text-yellow-300">
                        🟡 قريب (≤30 يوم)
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section class="bg-gradient-to-br from-success-50 to-success-100 dark:from-success-900/20 dark:to-success-900/30 border-2 border-success-300 dark:border-success-700">
                <div class="text-center">
                    <div class="text-4xl font-bold text-success-600 dark:text-success-400">
                        {{ $total }}
                    </div>
                    <div class="mt-2 text-sm font-medium text-success-700 dark:text-success-300">
                        📊 إجمالي نشط
                    </div>
                </div>
            </x-filament::section>
        </div>

        {{-- الجدول الرئيسي --}}
        <x-filament::section>
            {{ $this->table }}
        </x-filament::section>

        {{-- دليل الألوان --}}
        <x-filament::section>
            <x-slot name="heading">
                🎨 دليل الألوان
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div class="flex items-center gap-3 p-3 rounded-lg bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800">
                    <div class="w-4 h-4 rounded-full bg-danger-500 animate-pulse"></div>
                    <div>
                        <div class="font-bold text-danger-700 dark:text-danger-300">متأخر</div>
                        <div class="text-xs text-danger-600 dark:text-danger-400">التاريخ فات</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-lg bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800">
                    <div class="w-4 h-4 rounded-full bg-warning-500"></div>
                    <div>
                        <div class="font-bold text-warning-700 dark:text-warning-300">عاجل جداً</div>
                        <div class="text-xs text-warning-600 dark:text-warning-400">10 أيام أو أقل</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
                    <div class="w-4 h-4 rounded-full bg-yellow-500"></div>
                    <div>
                        <div class="font-bold text-yellow-700 dark:text-yellow-300">قريب</div>
                        <div class="text-xs text-yellow-600 dark:text-yellow-400">شهر إلى 3 أشهر</div>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-3 rounded-lg bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800">
                    <div class="w-4 h-4 rounded-full bg-success-500"></div>
                    <div>
                        <div class="font-bold text-success-700 dark:text-success-300">آمن</div>
                        <div class="text-xs text-success-600 dark:text-success-400">فوق 3 أشهر</div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
