<x-filament-panels::page>
    {{-- إحصائيات عامة --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white/10 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl p-4 border border-white/10">
            <div class="text-xs text-gray-400 mb-1">إجمالي الفخاخ</div>
            <div class="text-2xl font-bold text-amber-400">{{ $stats['total_traps'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">{{ $stats['active_traps'] ?? 0 }} نشط</div>
        </div>
        <div class="bg-white/10 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl p-4 border border-white/10">
            <div class="text-xs text-gray-400 mb-1">إجمالي التفاعلات</div>
            <div class="text-2xl font-bold text-blue-400">{{ $stats['total_interactions'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">{{ $stats['unique_users'] ?? 0 }} مستخدم فريد</div>
        </div>
        <div class="bg-white/10 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl p-4 border border-red-500/30">
            <div class="text-xs text-gray-400 mb-1">تصعيدات</div>
            <div class="text-2xl font-bold text-red-400">{{ $stats['escalated_count'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">{{ $stats['high_risk_count'] ?? 0 }} خطر مرتفع</div>
        </div>
        <div class="bg-white/10 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl p-4 border border-green-500/30">
            <div class="text-xs text-gray-400 mb-1">آخر 24 ساعة</div>
            <div class="text-2xl font-bold text-green-400">{{ $stats['last_24h'] ?? 0 }}</div>
            <div class="text-xs text-gray-500">{{ $stats['last_7d'] ?? 0 }} آخر 7 أيام</div>
        </div>
    </div>

    {{-- المستخدمين الأكثر خطورة --}}
    @if($highRiskUsers && $highRiskUsers->count() > 0)
    <div class="bg-white/5 dark:bg-gray-800/30 backdrop-blur-sm rounded-xl p-5 border border-red-500/20 mb-6">
        <h3 class="text-lg font-bold text-red-400 mb-4 flex items-center gap-2">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
            المستخدمين الأكثر خطورة
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-400 border-b border-white/10">
                    <tr>
                        <th class="text-right py-2 px-3">الموظف</th>
                        <th class="text-right py-2 px-3">الرقم الوظيفي</th>
                        <th class="text-right py-2 px-3">أعلى خطر</th>
                        <th class="text-right py-2 px-3">عدد التفاعلات</th>
                        <th class="text-right py-2 px-3">فخاخ مُفعَّلة</th>
                    </tr>
                </thead>
                <tbody class="text-gray-200">
                    @foreach($highRiskUsers as $entry)
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="py-2 px-3">{{ $entry->user->name ?? '—' }}</td>
                        <td class="py-2 px-3">{{ $entry->user->employee_id ?? '—' }}</td>
                        <td class="py-2 px-3">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                {{ $entry->max_risk >= 75 ? 'bg-red-500/20 text-red-400' :
                                   ($entry->max_risk >= 50 ? 'bg-amber-500/20 text-amber-400' : 'bg-blue-500/20 text-blue-400') }}">
                                {{ number_format($entry->max_risk, 1) }}%
                            </span>
                        </td>
                        <td class="py-2 px-3">{{ $entry->total_interactions }}</td>
                        <td class="py-2 px-3">{{ $entry->traps_triggered }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- آخر التفاعلات --}}
    <div class="bg-white/5 dark:bg-gray-800/30 backdrop-blur-sm rounded-xl p-5 border border-white/10">
        <h3 class="text-lg font-bold text-amber-400 mb-4 flex items-center gap-2">
            <x-heroicon-o-clock class="w-5 h-5" />
            آخر التفاعلات
        </h3>
        @if($recentInteractions && $recentInteractions->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-gray-400 border-b border-white/10">
                    <tr>
                        <th class="text-right py-2 px-3">التاريخ</th>
                        <th class="text-right py-2 px-3">الموظف</th>
                        <th class="text-right py-2 px-3">الفخ</th>
                        <th class="text-right py-2 px-3">الخطر</th>
                        <th class="text-right py-2 px-3">الإجراء</th>
                        <th class="text-right py-2 px-3">#</th>
                    </tr>
                </thead>
                <tbody class="text-gray-200">
                    @foreach($recentInteractions as $interaction)
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="py-2 px-3 text-xs text-gray-400">{{ $interaction->created_at->format('m/d H:i') }}</td>
                        <td class="py-2 px-3">{{ $interaction->user->name ?? '—' }}</td>
                        <td class="py-2 px-3">
                            <span class="px-2 py-1 bg-red-500/10 text-red-400 rounded text-xs">
                                {{ $interaction->trap->trap_code ?? '—' }}
                            </span>
                        </td>
                        <td class="py-2 px-3">
                            <span class="px-2 py-1 rounded-full text-xs font-bold
                                {{ $interaction->risk_score >= 75 ? 'bg-red-500/20 text-red-400' :
                                   ($interaction->risk_score >= 50 ? 'bg-amber-500/20 text-amber-400' :
                                   ($interaction->risk_score >= 25 ? 'bg-blue-500/20 text-blue-400' : 'bg-green-500/20 text-green-400')) }}">
                                {{ number_format($interaction->risk_score, 1) }}%
                            </span>
                        </td>
                        <td class="py-2 px-3">
                            @php
                                $actionLabel = match($interaction->action_taken) {
                                    'escalated' => 'مُصعَّد',
                                    'warned'    => 'مُحذَّر',
                                    default     => 'مُسجَّل',
                                };
                                $actionColor = match($interaction->action_taken) {
                                    'escalated' => 'text-red-400',
                                    'warned'    => 'text-amber-400',
                                    default     => 'text-gray-400',
                                };
                            @endphp
                            <span class="{{ $actionColor }} text-xs font-bold">{{ $actionLabel }}</span>
                        </td>
                        <td class="py-2 px-3 text-xs text-gray-500">{{ $interaction->interaction_count }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <x-heroicon-o-shield-check class="w-12 h-12 mx-auto mb-2 opacity-30" />
            <p>لا توجد تفاعلات مسجلة بعد</p>
        </div>
        @endif
    </div>
</x-filament-panels::page>
