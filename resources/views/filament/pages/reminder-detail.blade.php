<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">الموظف</div>
            <div class="mt-1 text-lg font-bold">{{ $record->user->name_ar }}</div>
        </div>

        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">رقم الموظف</div>
            <div class="mt-1 text-lg">{{ $record->user->employee_id }}</div>
        </div>

        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">الفرع</div>
            <div class="mt-1 text-lg">{{ $record->user->branch->name_ar ?? '-' }}</div>
        </div>

        <div>
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">التاريخ</div>
            <div class="mt-1 text-lg font-bold">{{ $record->reminder_date->format('Y-m-d') }}</div>
        </div>

        <div class="col-span-2">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">الحالة</div>
            <div class="mt-1">
                <x-filament::badge :color="$record->status_color" size="lg">
                    {{ $record->status_label }}
                </x-filament::badge>
            </div>
        </div>
    </div>

    @if($record->notes)
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">ملاحظات</div>
            <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                {{ $record->notes }}
            </div>
        </div>
    @endif

    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="text-sm text-gray-500 dark:text-gray-400">
            أنشئ بواسطة: {{ $record->creator->name_ar ?? 'النظام' }} • 
            {{ $record->created_at->diffForHumans() }}
        </div>
    </div>
</div>
