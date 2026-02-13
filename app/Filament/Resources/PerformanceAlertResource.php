<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerformanceAlertResource\Pages;
use App\Models\PerformanceAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PerformanceAlertResource extends Resource
{
    protected static ?string $model = PerformanceAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?int $navigationSort = 15;

    /**
     * Access control: security_level >= 5 or super_admin.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->is_super_admin || $user->security_level >= 5);
    }

    /**
     * Hide navigation for unauthorized users.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('users.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return 'التنبيهات';
    }

    public static function getModelLabel(): string
    {
        return 'تنبيه';
    }

    public static function getPluralModelLabel(): string
    {
        return 'التنبيهات';
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) PerformanceAlert::where('is_read', false)->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Branch scope: non-super-admin sees only alerts for their branch employees.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && ! $user->is_super_admin && $user->security_level < 10 && $user->branch_id) {
            $query->whereHas('user', fn (Builder $q) => $q->where('branch_id', $user->branch_id));
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('الموظف')
                ->relationship('user', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),

            Forms\Components\Select::make('alert_type')
                ->label('نوع التنبيه')
                ->options([
                    'badge_earned'       => 'شارة مكتسبة',
                    'circular'           => 'تعميم',
                    'late_warning'       => 'تحذير تأخر',
                    'absence_warning'    => 'تحذير غياب',
                    'performance_review' => 'مراجعة أداء',
                ])
                ->required(),

            Forms\Components\Select::make('severity')
                ->label('الأهمية')
                ->options([
                    'info'    => 'معلومات',
                    'success' => 'نجاح',
                    'warning' => 'تحذير',
                    'danger'  => 'خطر',
                ])
                ->required(),

            Forms\Components\TextInput::make('title_ar')
                ->label('العنوان (عربي)')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('title_en')
                ->label('العنوان (إنجليزي)')
                ->maxLength(255),

            Forms\Components\Textarea::make('message_ar')
                ->label('الرسالة (عربي)')
                ->required(),

            Forms\Components\Textarea::make('message_en')
                ->label('الرسالة (إنجليزي)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name_ar')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('alert_type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'badge_earned'       => 'success',
                        'circular'           => 'info',
                        'late_warning'       => 'warning',
                        'absence_warning'    => 'danger',
                        'performance_review' => 'primary',
                        default              => 'gray',
                    }),

                Tables\Columns\TextColumn::make('severity')
                    ->label('الأهمية')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'info'    => 'info',
                        'success' => 'success',
                        'warning' => 'warning',
                        'danger'  => 'danger',
                        default   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('title_ar')
                    ->label('العنوان')
                    ->limit(50),

                Tables\Columns\IconColumn::make('is_read')
                    ->label('مقروء')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('alert_type')
                    ->label('النوع')
                    ->options([
                        'badge_earned'       => 'شارة مكتسبة',
                        'circular'           => 'تعميم',
                        'late_warning'       => 'تحذير تأخر',
                        'absence_warning'    => 'تحذير غياب',
                        'performance_review' => 'مراجعة أداء',
                    ]),

                Tables\Filters\TernaryFilter::make('is_read')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('مقروء')
                    ->falseLabel('غير مقروء'),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_as_read')
                    ->label('تحديد كمقروء')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PerformanceAlert $record): bool => ! $record->is_read)
                    ->action(fn (PerformanceAlert $record) => $record->update([
                        'is_read'      => true,
                        'read_at'      => now(),
                        'dismissed_by' => auth()->id(),
                    ])),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_mark_read')
                        ->label('تحديد الكل كمقروء')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each(fn ($r) => $r->update([
                            'is_read'      => true,
                            'read_at'      => now(),
                            'dismissed_by' => auth()->id(),
                        ])))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerformanceAlerts::route('/'),
        ];
    }
}
