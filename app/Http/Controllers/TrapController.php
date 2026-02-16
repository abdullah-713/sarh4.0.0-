<?php

namespace App\Http\Controllers;

use App\Models\Trap;
use App\Services\TrapResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * وحدة التحكم بالفخاخ النفسية
 *
 * يعالج محاولات تفعيل الفخاخ ويُرجع استجابة وهمية
 * بينما يُسجل التفاعل سراً في الخلفية.
 */
class TrapController extends Controller
{
    public function __construct(
        private TrapResponseService $trapService
    ) {}

    /**
     * POST /traps/trigger
     *
     * يستقبل طلب تفعيل فخ ويُرجع استجابة وهمية "ناجحة"
     * بينما يُسجل التفاعل ويحسب درجة الخطر.
     */
    public function trigger(Request $request): JsonResponse
    {
        $request->validate([
            'trap_code' => 'required|string|exists:traps,trap_code',
        ]);

        $trap = Trap::where('trap_code', $request->trap_code)
            ->active()
            ->first();

        if (! $trap) {
            // لا نكشف أن الفخ غير موجود أو غير نشط
            return response()->json([
                'status'  => 'success',
                'message' => 'تم تنفيذ العملية بنجاح',
            ]);
        }

        $user = $request->user();

        // لا نُفعّل الفخ ضد المالك (مستوى 10)
        if ($user->security_level >= 10) {
            return response()->json($this->trapService->getFakeResponse($trap));
        }

        // معالجة التفاعل
        $this->trapService->processInteraction($trap, $user, $request);

        // إرجاع الاستجابة الوهمية
        return response()->json($this->trapService->getFakeResponse($trap));
    }
}
