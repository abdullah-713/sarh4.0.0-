<x-filament-panels::page>
    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Form Section --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <form wire:submit.prevent="generatePreview">
        {{ $this->form }}

        {{-- ── Action Buttons ─────────────────────────────────── --}}
        <div class="mt-6 flex flex-wrap gap-3">
            {{-- Preview --}}
            <x-filament::button
                type="submit"
                icon="heroicon-o-eye"
                color="info"
                size="lg"
            >
                معاينة قبل التوليد
            </x-filament::button>

            {{-- Commit --}}
            <x-filament::button
                wire:click="commitRecords"
                wire:confirm="⚠️ هل أنت متأكد؟ سيتم إدراج سجلات حضور تجريبية في قاعدة البيانات."
                icon="heroicon-o-arrow-down-tray"
                color="success"
                size="lg"
            >
                توليد وحفظ البيانات
            </x-filament::button>

            {{-- Wipe (Dangerous) --}}
            <x-filament::button
                wire:click="wipeRecords"
                wire:confirm="🔴 تحذير خطير! سيتم حذف جميع سجلات الحضور للفترة والفروع المحددة نهائياً. هل أنت متأكد تماماً؟"
                icon="heroicon-o-trash"
                color="danger"
                size="lg"
            >
                مسح البيانات للفترة المحددة
            </x-filament::button>
        </div>
    </form>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- Preview Results Panel --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($showPreview && !empty($previewStats))
        <div class="mt-8 rounded-xl border border-orange-200 bg-orange-50 dark:bg-gray-800 dark:border-orange-700 p-6 space-y-6">
            {{-- Header --}}
            <div class="flex items-center gap-3">
                <x-heroicon-o-chart-bar-square class="w-8 h-8 text-orange-500" />
                <h2 class="text-xl font-bold text-orange-700 dark:text-orange-400">
                    معاينة البيانات المتوقعة
                </h2>
            </div>

            {{-- Summary Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                {{-- Working Days --}}
                <div class="rounded-lg bg-white dark:bg-gray-900 p-4 text-center shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-3xl font-bold text-orange-600">{{ $previewStats['working_days'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">يوم عمل</div>
                </div>

                {{-- Total Users --}}
                <div class="rounded-lg bg-white dark:bg-gray-900 p-4 text-center shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-3xl font-bold text-blue-600">{{ $previewStats['total_users'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">موظف</div>
                </div>

                {{-- Total Records --}}
                <div class="rounded-lg bg-white dark:bg-gray-900 p-4 text-center shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-3xl font-bold text-emerald-600">{{ number_format($previewStats['total_records']) }}</div>
                    <div class="text-sm text-gray-500 mt-1">سجل متوقع</div>
                </div>

                {{-- Gauge --}}
                <div class="rounded-lg bg-white dark:bg-gray-900 p-4 text-center shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="text-3xl font-bold {{ $previewStats['gauge'] >= 7 ? 'text-emerald-600' : ($previewStats['gauge'] >= 4 ? 'text-amber-500' : 'text-red-600') }}">
                        {{ $previewStats['gauge'] }}/10
                    </div>
                    <div class="text-sm text-gray-500 mt-1">مستوى الانضباط</div>
                </div>
            </div>

            {{-- Estimated Distribution --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-x-circle class="w-5 h-5 text-red-500" />
                        <span class="font-semibold text-red-700 dark:text-red-400">نسبة الغياب المتوقعة</span>
                    </div>
                    <div class="text-2xl font-bold text-red-600 mt-2">{{ $previewStats['estimated_absent'] }}%</div>
                </div>

                <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 p-4 border border-amber-200 dark:border-amber-800">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-clock class="w-5 h-5 text-amber-500" />
                        <span class="font-semibold text-amber-700 dark:text-amber-400">نسبة التأخير المتوقعة</span>
                    </div>
                    <div class="text-2xl font-bold text-amber-600 mt-2">{{ $previewStats['estimated_late'] }}%</div>
                </div>

                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-arrow-right-start-on-rectangle class="w-5 h-5 text-blue-500" />
                        <span class="font-semibold text-blue-700 dark:text-blue-400">نسبة الانصراف المبكر</span>
                    </div>
                    <div class="text-2xl font-bold text-blue-600 mt-2">{{ $previewStats['estimated_early'] }}%</div>
                </div>
            </div>

            {{-- Branches Breakdown --}}
            @if(!empty($previewStats['branches']))
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-3">
                        <x-heroicon-o-building-office-2 class="w-5 h-5 inline-block ml-1" />
                        تفاصيل الفروع
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100 dark:bg-gray-700">
                                    <th class="px-4 py-2 text-right font-semibold">الفرع</th>
                                    <th class="px-4 py-2 text-right font-semibold">الرمز</th>
                                    <th class="px-4 py-2 text-right font-semibold">عدد الموظفين</th>
                                    <th class="px-4 py-2 text-right font-semibold">السجلات المتوقعة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previewStats['branches'] as $bs)
                                    <tr class="border-b dark:border-gray-600">
                                        <td class="px-4 py-2 font-medium">{{ $bs['name'] }}</td>
                                        <td class="px-4 py-2">
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                                                {{ $bs['code'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">{{ $bs['users'] }}</td>
                                        <td class="px-4 py-2">{{ number_format($bs['users'] * $previewStats['working_days']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Existing Records Warning --}}
            @if(($previewStats['existing_records'] ?? 0) > 0)
                <div class="rounded-lg bg-amber-100 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700 p-4">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600" />
                        <span class="font-semibold text-amber-700 dark:text-amber-400">
                            يوجد {{ number_format($previewStats['existing_records']) }} سجل موجود مسبقاً في هذه الفترة — لن يتم تكرارها.
                        </span>
                    </div>
                    <div class="text-sm text-amber-600 dark:text-amber-300 mt-1">
                        سجلات جديدة فعلية: {{ number_format($previewStats['net_new_records']) }}
                    </div>
                </div>
            @endif
        </div>
    @endif
</x-filament-panels::page>
