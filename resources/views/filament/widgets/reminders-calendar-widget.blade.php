<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            📅 تقويم التنبيهات والمواعيد المهمة
        </x-slot>

        <x-slot name="headerEnd">
            <div class="flex gap-4 text-sm">
                <span class="px-3 py-1 rounded-lg bg-danger-50 text-danger-600 dark:bg-danger-900/20 dark:text-danger-400">
                    عاجل: {{ $this->getUrgentCount() }}
                </span>
                <span class="px-3 py-1 rounded-lg bg-warning-50 text-warning-600 dark:bg-warning-900/20 dark:text-warning-400">
                    متأخر: {{ $this->getOverdueCount() }}
                </span>
            </div>
        </x-slot>

        <div class="space-y-3">
            @forelse($this->getReminders() as $reminder)
                <div class="flex items-center justify-between p-4 rounded-lg border transition-all hover:shadow-md
                    @if($reminder['status_color'] === 'danger' && $reminder['days_until'] <= 10)
                        border-danger-300 bg-danger-50 dark:border-danger-700 dark:bg-danger-900/20 animate-pulse
                    @elseif($reminder['status_color'] === 'danger')
                        border-danger-200 bg-danger-50/50 dark:border-danger-800 dark:bg-danger-900/10
                    @elseif($reminder['status_color'] === 'warning')
                        border-warning-200 bg-warning-50/50 dark:border-warning-800 dark:bg-warning-900/10
                    @else
                        border-success-200 bg-success-50/50 dark:border-success-800 dark:bg-success-900/10
                    @endif
                ">
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <span class="text-lg font-bold
                                @if($reminder['status_color'] === 'danger') text-danger-600 dark:text-danger-400
                                @elseif($reminder['status_color'] === 'warning') text-warning-600 dark:text-warning-400
                                @else text-success-600 dark:text-success-400
                                @endif
                            ">
                                {{ $reminder['employee'] }}
                            </span>
                            
                            @if($reminder['is_urgent'])
                                <x-filament::badge color="danger" size="sm">
                                    عاجل جداً
                                </x-filament::badge>
                            @elseif($reminder['is_overdue'])
                                <x-filament::badge color="danger" size="sm">
                                    متأخر
                                </x-filament::badge>
                            @endif
                        </div>
                        
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            📋 {{ $reminder['key'] }}
                        </div>
                    </div>

                    <div class="text-left">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $reminder['date'] }}
                        </div>
                        <div class="mt-1">
                            <x-filament::badge :color="$reminder['status_color']" size="sm">
                                {{ $reminder['status_label'] }}
                            </x-filament::badge>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-gray-500">
                    <div class="text-6xl mb-4">🎉</div>
                    <p class="text-lg font-medium">لا توجد تنبيهات قادمة</p>
                    <p class="text-sm mt-2">جميع المواعيد مكتملة أو بعيدة</p>
                </div>
            @endforelse
        </div>

        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                <div class="flex gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-danger-500"></div>
                        <span>≤ 30 يوم</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-warning-500"></div>
                        <span>30-90 يوم</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-success-500"></div>
                        <span>> 90 يوم</span>
                    </div>
                </div>
                <div>
                    عرض {{ $this->getReminders()->count() }} من أصل 50 تنبيه
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
