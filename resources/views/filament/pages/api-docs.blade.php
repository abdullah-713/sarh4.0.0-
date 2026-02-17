<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
                <x-heroicon-o-code-bracket class="w-8 h-8 text-primary-500" />
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">توثيق نقاط API — SarhIndex v4.0</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">جميع نقاط الاتصال المتاحة للتطبيق</p>
                </div>
            </div>
        </div>

        {{-- Attendance Endpoints --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📍 الحضور والانصراف</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-right py-3 px-4 font-semibold">الطريقة</th>
                            <th class="text-right py-3 px-4 font-semibold">المسار</th>
                            <th class="text-right py-3 px-4 font-semibold">الوصف</th>
                            <th class="text-right py-3 px-4 font-semibold">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded text-xs font-mono">POST</span></td>
                            <td class="py-3 px-4 font-mono text-xs">/attendance/check-in</td>
                            <td class="py-3 px-4">تسجيل حضور متزامن</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">201</span></td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded text-xs font-mono">POST</span></td>
                            <td class="py-3 px-4 font-mono text-xs">/attendance/queue-check-in</td>
                            <td class="py-3 px-4">تسجيل حضور غير متزامن (Queue)</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">202</span></td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded text-xs font-mono">POST</span></td>
                            <td class="py-3 px-4 font-mono text-xs">/attendance/check-out</td>
                            <td class="py-3 px-4">تسجيل انصراف</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">200</span></td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded text-xs font-mono">GET</span></td>
                            <td class="py-3 px-4 font-mono text-xs">/attendance/today</td>
                            <td class="py-3 px-4">حالة حضور اليوم</td>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">200</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Request/Response Examples --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📦 أمثلة الطلبات والاستجابات</h3>
            
            <div class="space-y-4">
                <div>
                    <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">POST /attendance/check-in</h4>
                    <pre class="bg-gray-900 text-green-400 rounded-lg p-4 text-xs overflow-x-auto" dir="ltr">
// Request Body
{
    "latitude": 24.7136,
    "longitude": 46.6753
}

// Response 201
{
    "message": "تم تسجيل الحضور بنجاح",
    "data": {
        "id": 1,
        "attendance_date": "2026-02-13",
        "status": "present",
        "check_in_at": "2026-02-13T08:00:00+03:00",
        "delay_minutes": 0,
        "cost_per_minute": 0.7576
    }
}
                    </pre>
                </div>

                <div>
                    <h4 class="font-semibold text-gray-700 dark:text-gray-300 mb-2">POST /attendance/queue-check-in</h4>
                    <pre class="bg-gray-900 text-green-400 rounded-lg p-4 text-xs overflow-x-auto" dir="ltr">
// Response 202
{
    "status": "processing",
    "message": "جاري معالجة طلب الحضور"
}
                    </pre>
                </div>
            </div>
        </div>

        {{-- Error Codes --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">⚠️ أكواد الأخطاء</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-right py-3 px-4 font-semibold">الكود</th>
                            <th class="text-right py-3 px-4 font-semibold">الوصف</th>
                            <th class="text-right py-3 px-4 font-semibold">مثال</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">422</span></td>
                            <td class="py-3 px-4">خطأ في البيانات المرسلة أو خارج النطاق الجغرافي</td>
                            <td class="py-3 px-4 font-mono text-xs">{"message": "أنت خارج نطاق الفرع"}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">404</span></td>
                            <td class="py-3 px-4">المورد المطلوب غير موجود</td>
                            <td class="py-3 px-4 font-mono text-xs">{"message": "المورد المطلوب غير موجود"}</td>
                        </tr>
                        <tr>
                            <td class="py-3 px-4"><span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">500</span></td>
                            <td class="py-3 px-4">خطأ داخلي في الخادم</td>
                            <td class="py-3 px-4 font-mono text-xs">{"message": "حدث خطأ داخلي في الخادم"}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
