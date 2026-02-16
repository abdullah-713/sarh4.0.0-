<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrapInteractionResource\Pages;
use App\Models\TrapInteraction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrapInteractionResource extends Resource
{
    protected static ?string $model = TrapInteraction::class;

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationGroup = 'الأمان';

    protected static ?string $navigationLabel = 'سجل التفاعلات';

    protected static ?string $modelLabel = 'تفاعل';

    protected static ?string $pluralModelLabel = 'تفاعلات الفخاخ';

    protected static ?int $navigationSort = 91;

    /**
     * مستوى أمان 10 فقط (God Mode)
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->is_super_admin || $user->security_level >= 10);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('trap.trap_code')
                    ->label('رمز الفخ')
                    ->badge()
                    ->color('danger')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('الموظف')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.employee_id')
                    ->label('الرقم الوظيفي'),

                Tables\Columns\TextColumn::make('risk_score')
                    ->label('درجة الخطر')
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 75 => 'danger',
                        $state >= 50 => 'warning',
                        $state >= 25 => 'info',
                        default      => 'success',
                    })
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('action_taken')
                    ->label('الإجراء')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'logged'    => 'مُسجَّل',
                        'warned'    => 'مُحذَّر',
                        'escalated' => 'مُصعَّد',
                        default     => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'escalated' => 'danger',
                        'warned'    => 'warning',
                        default     => 'gray',
                    }),

                Tables\Columns\TextColumn::make('interaction_count')
                    ->label('رقم التفاعل')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action_taken')
                    ->label('الإجراء')
                    ->options([
                        'logged'    => 'مُسجَّل',
                        'warned'    => 'مُحذَّر',
                        'escalated' => 'مُصعَّد',
                    ]),
                Tables\Filters\SelectFilter::make('trap_id')
                    ->label('الفخ')
                    ->relationship('trap', 'name'),
                Tables\Filters\Filter::make('high_risk')
                    ->label('خطر مرتفع فقط')
                    ->query(fn ($query) => $query->where('risk_score', '>=', 50)),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false; // قراءة فقط
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrapInteractions::route('/'),
        ];
    }
}
