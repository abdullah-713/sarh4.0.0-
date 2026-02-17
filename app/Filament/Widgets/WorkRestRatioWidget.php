<?php

namespace App\Filament\Widgets;

use App\Models\WorkRestStat;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * SarhIndex v4.1 — Work/Rest Ratio Widget (نسبة العمل/الراحة)
 *
 * يعرض جدول الموظفين مع نسبة الإنتاجية اليومية.
 * يظهر فقط لمستوى أمان 6+ (مدير فرع فأعلى).
 */
class WorkRestRatioWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '📊 نسبة العمل / الراحة — اليوم';

    protected static ?int $sort = 15;

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && ($user->is_super_admin || $user->security_level >= 6);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('work_minutes')
                    ->label('عمل فعلي')
                    ->formatStateUsing(fn ($state) => round($state) . ' د')
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rest_minutes')
                    ->label('راحة')
                    ->formatStateUsing(fn ($state) => round($state) . ' د')
                    ->color('warning')
                    ->sortable(),

                Tables\Columns\TextColumn::make('productivity_ratio')
                    ->label('نسبة الإنتاجية')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color(fn ($state): string => match (true) {
                        (float) $state >= 80 => 'success',
                        (float) $state >= 60 => 'warning',
                        default               => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('rating')
                    ->label('التقييم')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'golden'   => '🏆 ذهبي',
                        'normal'   => '✅ طبيعي',
                        'leaking'  => '🟡 مستنزف',
                        'critical' => '🔴 حرج',
                        default    => $state,
                    })
                    ->color(fn ($state): string => match ($state) {
                        'golden'   => 'success',
                        'normal'   => 'info',
                        'leaking'  => 'warning',
                        'critical' => 'danger',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('vpm_leak')
                    ->label('خسارة (ر.س)')
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->color('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('anomaly_readings')
                    ->label('شذوذ')
                    ->badge()
                    ->color(fn ($state): string => $state > 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} ⚠️" : '✓')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([
                        'golden'   => '🏆 ذهبي',
                        'normal'   => '✅ طبيعي',
                        'leaking'  => '🟡 مستنزف',
                        'critical' => '🔴 حرج',
                    ]),
            ])
            ->defaultSort('productivity_ratio', 'asc');
    }

    private function getQuery(): Builder
    {
        $query = WorkRestStat::query()
            ->where('stat_date', today())
            ->with('user');

        $user = auth()->user();

        // تقييد حسب الفرع (مستوى < 10)
        if ($user && ! $user->is_super_admin && $user->security_level < 10) {
            $query->whereHas('user', fn ($q) => $q->where('branch_id', $user->branch_id));
        }

        return $query;
    }
}
