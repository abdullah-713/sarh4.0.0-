<?php

namespace App\Services;

use App\Events\TrapTriggered;
use App\Models\AuditLog;
use App\Models\Trap;
use App\Models\TrapInteraction;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * خدمة معالجة استجابات الفخاخ — Trap Response Service
 *
 * المسؤوليات:
 * 1. تسجيل التفاعل مع الفخ
 * 2. حساب درجة الخطر (لوغاريتمية تصاعدية)
 * 3. تحديد الإجراء المناسب (logged → warned → escalated)
 * 4. إنشاء سجل تدقيق
 *
 * معادلة الخطر:
 *   risk = min(100, base_risk × 2^(interaction_count - 1) × risk_weight)
 *
 * حيث:
 *   base_risk = 10 (القيمة الأولية)
 *   interaction_count = عدد تفاعلات المستخدم مع نفس الفخ
 *   risk_weight = وزن الفخ (1.0 - 5.0)
 */
class TrapResponseService
{
    private const BASE_RISK = 10;

    /**
     * معالجة تفاعل مستخدم مع فخ
     */
    public function processInteraction(Trap $trap, User $user, Request $request): TrapInteraction
    {
        // حساب عدد التفاعلات السابقة لهذا المستخدم مع هذا الفخ
        $previousCount = TrapInteraction::where('trap_id', $trap->id)
            ->where('user_id', $user->id)
            ->count();

        $interactionCount = $previousCount + 1;

        // حساب درجة الخطر (لوغاريتمية تصاعدية)
        $riskScore = $this->calculateRiskScore($interactionCount, $trap->risk_weight);

        // تحديد الإجراء المناسب
        $action = $this->determineAction($riskScore);

        // تسجيل التفاعل
        $interaction = TrapInteraction::create([
            'trap_id'           => $trap->id,
            'user_id'           => $user->id,
            'risk_score'        => $riskScore,
            'action_taken'      => $action,
            'ip_address'        => $request->ip(),
            'user_agent'        => $request->userAgent(),
            'referrer_url'      => $request->header('referer'),
            'interaction_count' => $interactionCount,
            'metadata'          => [
                'trigger_type'    => $trap->trigger_type,
                'placement'       => $trap->placement,
                'security_level'  => $user->security_level,
                'timestamp'       => now()->toIso8601String(),
            ],
        ]);

        // إنشاء سجل تدقيق
        AuditLog::record(
            action: "trap_triggered:{$trap->trap_code}",
            model: $interaction,
            newValues: [
                'trap_code'   => $trap->trap_code,
                'trap_name'   => $trap->name,
                'risk_score'  => $riskScore,
                'action'      => $action,
                'count'       => $interactionCount,
            ]
        );

        // إطلاق حدث TrapTriggered للمعالجة غير المتزامنة
        TrapTriggered::dispatch($trap, $user, $interaction);

        return $interaction;
    }

    /**
     * حساب درجة الخطر بمعادلة لوغاريتمية تصاعدية
     *
     * risk = min(100, BASE_RISK × 2^(count - 1) × weight)
     */
    private function calculateRiskScore(int $interactionCount, float $riskWeight): float
    {
        $raw = self::BASE_RISK * pow(2, $interactionCount - 1) * $riskWeight;
        return round(min(100.0, $raw), 2);
    }

    /**
     * تحديد الإجراء بناءً على درجة الخطر
     */
    private function determineAction(float $riskScore): string
    {
        return match (true) {
            $riskScore >= 75 => 'escalated',
            $riskScore >= 40 => 'warned',
            default          => 'logged',
        };
    }

    /**
     * الحصول على الاستجابة الوهمية الآمنة للفخ
     */
    public function getFakeResponse(Trap $trap): array
    {
        return $trap->fake_response ?? [
            'status'  => 'success',
            'message' => 'تم تنفيذ العملية بنجاح',
        ];
    }

    /**
     * الحصول على المستخدمين الأكثر خطورة
     */
    public function getHighRiskUsers(int $limit = 10): \Illuminate\Support\Collection
    {
        return TrapInteraction::select('user_id')
            ->selectRaw('MAX(risk_score) as max_risk')
            ->selectRaw('COUNT(*) as total_interactions')
            ->selectRaw('COUNT(DISTINCT trap_id) as traps_triggered')
            ->groupBy('user_id')
            ->orderByDesc('max_risk')
            ->limit($limit)
            ->with('user:id,name,employee_id,security_level')
            ->get();
    }

    /**
     * إحصائيات عامة عن الفخاخ
     */
    public function getStatistics(): array
    {
        return [
            'total_traps'        => Trap::count(),
            'active_traps'       => Trap::active()->count(),
            'total_interactions'  => TrapInteraction::count(),
            'escalated_count'    => TrapInteraction::escalated()->count(),
            'high_risk_count'    => TrapInteraction::highRisk()->count(),
            'unique_users'       => TrapInteraction::distinct('user_id')->count('user_id'),
            'last_24h'           => TrapInteraction::where('created_at', '>=', now()->subDay())->count(),
            'last_7d'            => TrapInteraction::where('created_at', '>=', now()->subWeek())->count(),
        ];
    }
}
