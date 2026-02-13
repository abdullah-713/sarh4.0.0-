<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LossAlertResource\Pages;
use App\Models\LossAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class LossAlertResource extends Resource
{
    protected static ?string $model = LossAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationLabel = 'تنبيهات الخسائر';

    protected static ?string $modelLabel = 'تنبيه خسارة';

    protected static ?string $pluralModelLabel = 'تنبيهات الخسائر';

    protected static ?string $navigationGroup = 'التحليلات';

    protected static ?int $navigationSort = 22;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->is_super_admin || $user->security_level >= 10);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات التنبيه')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('branch_id')
                        ->label('الفرع')
                        ->relationship('branch', 'name_ar')
                        ->required(),
                    Forms\Components\DatePicker::make('alert_date')
                        ->label('التاريخ')
                        ->required(),
                    Forms\Components\Select::make('alert_type')
                        ->label('النوع')
                        ->options([
                            'threshold_exceeded' => 'تجاوز الحد',
                            'low_attendance'     => 'انخفاض حضور',
                            'mass_late'          => 'تأخير جماعي',
                            'pattern_detected'   => 'نمط مكتشف',
                            'anomaly'            => 'شذوذ',
                        ])
                        ->required(),
                    Forms\Components\Select::make('severity')
                        ->label('الخطورة')
                        ->options([
                            'low'      => 'منخفض',
                            'medium'   => 'متوسط',
                            'high'     => 'عالي',
                            'critical' => 'حرج',
                        ])
                        ->required(),
                    Forms\Components\Textarea::make('description_ar')
                        ->label('الوصف')
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('resolution_notes')
                        ->label('ملاحظات الحل')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch.name_ar')
                    ->label('الفرع')
                    ->sortable(),

                Tables\Columns\TextColumn::make('alert_date')
                    ->label('التاريخ')
                    ->date()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('alert_type')
                    ->label('النوع')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'threshold_exceeded' => 'تجاوز الحد',
                        'low_attendance'     => 'انخفاض حضور',
                        'mass_late'          => 'تأخير جماعي',
                        'pattern_detected'   => 'نمط مكتشف',
                        default              => $state,
                    }),

                Tables\Columns\BadgeColumn::make('severity')
                    ->label('الخطورة')
                    ->colors([
                        'danger'  => 'critical',
                        'warning' => 'high',
                        'info'    => 'medium',
                        'gray'    => 'low',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'critical' => 'حرج',
                        'high'     => 'عالي',
                        'medium'   => 'متوسط',
                        'low'      => 'منخفض',
                        default    => $state,
                    }),

                Tables\Columns\TextColumn::make('actual_value')
                    ->label('القيمة الفعلية')
                    ->suffix('%')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('threshold_value')
                    ->label('الحد')
                    ->suffix('%'),

                Tables\Columns\IconColumn::make('is_acknowledged')
                    ->label('مطّلع')
                    ->boolean(),

                Tables\Columns\TextColumn::make('description_ar')
                    ->label('الوصف')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description_ar),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('severity')
                    ->label('الخطورة')
                    ->options([
                        'critical' => 'حرج',
                        'high'     => 'عالي',
                        'medium'   => 'متوسط',
                        'low'      => 'منخفض',
                    ]),
                Tables\Filters\TernaryFilter::make('is_acknowledged')
                    ->label('حالة الاطلاع'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Action::make('acknowledge')
                    ->label('اطّلعت')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (LossAlert $record) => !$record->is_acknowledged)
                    ->action(function (LossAlert $record) {
                        $record->acknowledge(auth()->id());
                        Notification::make()
                            ->title('تم التأكيد')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('alert_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLossAlerts::route('/'),
        ];
    }
}
