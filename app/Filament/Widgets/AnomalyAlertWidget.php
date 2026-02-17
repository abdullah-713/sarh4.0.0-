<?php

namespace App\Filament\Widgets;

use App\Models\AnomalyLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * SarhIndex v4.1 — Anomaly Alert Widget (تنبيهات التلاعب)
 *
 * يعرض آخر حالات التلاعب المكتشفة — للمراجعة الفورية.
 * يظهر فقط لمستوى أمان 7+ (مدير إقليمي فأعلى).
 */
class AnomalyAlertWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '🕵️ تنبيهات التلاعب — آخر 24 ساعة';

    protected static ?int $sort = 16;

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && ($user->is_super_admin || $user->security_level >= 7);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الموظف')
                    ->searchable(),

                Tables\Columns\TextColumn::make('anomaly_type')
                    ->label('نوع التلاعب')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'location_mismatch'    => '📍 تناقض موقع',
                        'perfect_signal'       => '🤖 إشارة آلية',
                        'no_motion_timeout'    => '💤 ثبات طويل',
                        'frequency_mismatch'   => '📡 تردد غير متوافق',
                        'replay_attack'        => '🔄 قراءات معادة',
                        'impossible_frequency' => '⚡ تردد مستحيل',
                        default                => $state,
                    })
                    ->badge()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('confidence')
                    ->label('الثقة')
                    ->formatStateUsing(fn ($state) => round($state * 100) . '%')
                    ->badge()
                    ->color(fn ($state): string => $state >= 0.9 ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('الوقت')
                    ->dateTime('H:i')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_reviewed')
                    ->label('مُراجع')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->actions([
                Tables\Actions\Action::make('review')
                    ->label('مُراجع ✓')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (AnomalyLog $record) => $record->is_reviewed)
                    ->action(fn (AnomalyLog $record) => $record->markReviewed()),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('anomaly_type')
                    ->label('النوع')
                    ->options([
                        'location_mismatch'    => '📍 تناقض موقع',
                        'perfect_signal'       => '🤖 إشارة آلية',
                        'no_motion_timeout'    => '💤 ثبات طويل',
                        'frequency_mismatch'   => '📡 تردد غير متوافق',
                        'replay_attack'        => '🔄 قراءات معادة',
                        'impossible_frequency' => '⚡ تردد مستحيل',
                    ]),
                Tables\Filters\TernaryFilter::make('is_reviewed')
                    ->label('حالة المراجعة'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private function getQuery(): Builder
    {
        $query = AnomalyLog::query()
            ->where('created_at', '>', now()->subDay())
            ->with('user');

        $user = auth()->user();

        if ($user && ! $user->is_super_admin && $user->security_level < 10) {
            $query->whereHas('user', fn ($q) => $q->where('branch_id', $user->branch_id));
        }

        return $query;
    }
}
