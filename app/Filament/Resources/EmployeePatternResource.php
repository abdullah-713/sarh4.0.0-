<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeePatternResource\Pages;
use App\Models\EmployeePattern;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeePatternResource extends Resource
{
    protected static ?string $model = EmployeePattern::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationLabel = 'أنماط الموظفين';

    protected static ?string $modelLabel = 'نمط موظف';

    protected static ?string $pluralModelLabel = 'أنماط الموظفين';

    protected static ?string $navigationGroup = 'التحليلات';

    protected static ?int $navigationSort = 23;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->is_super_admin || $user->security_level >= 10);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('البيانات')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('الموظف')
                        ->relationship('user', 'name_ar')
                        ->searchable()
                        ->required(),
                    Forms\Components\Select::make('pattern_type')
                        ->label('نوع النمط')
                        ->options(EmployeePattern::patternTypes())
                        ->required(),
                    Forms\Components\TextInput::make('frequency_score')
                        ->label('درجة التكرار')
                        ->numeric()
                        ->suffix('%'),
                    Forms\Components\TextInput::make('financial_impact')
                        ->label('الأثر المالي')
                        ->numeric()
                        ->prefix('ر.س'),
                    Forms\Components\Select::make('risk_level')
                        ->label('مستوى الخطر')
                        ->options([
                            'low'      => 'منخفض',
                            'medium'   => 'متوسط',
                            'high'     => 'عالي',
                            'critical' => 'حرج',
                        ]),
                    Forms\Components\Toggle::make('is_active')
                        ->label('نشط'),
                    Forms\Components\Textarea::make('description_ar')
                        ->label('الوصف')
                        ->columnSpanFull(),
                ]),
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

                Tables\Columns\TextColumn::make('branch.name_ar')
                    ->label('الفرع')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('pattern_type')
                    ->label('النمط')
                    ->formatStateUsing(fn (string $state) => EmployeePattern::patternTypes()[$state] ?? $state),

                Tables\Columns\TextColumn::make('frequency_score')
                    ->label('التكرار')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => $state >= 70 ? 'danger' : ($state >= 40 ? 'warning' : 'success')),

                Tables\Columns\TextColumn::make('financial_impact')
                    ->label('الأثر المالي')
                    ->money('SAR')
                    ->sortable()
                    ->color('danger'),

                Tables\Columns\BadgeColumn::make('risk_level')
                    ->label('الخطر')
                    ->colors([
                        'danger'  => 'critical',
                        'warning' => 'high',
                        'info'    => 'medium',
                        'success' => 'low',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'critical' => 'حرج',
                        'high'     => 'عالي',
                        'medium'   => 'متوسط',
                        'low'      => 'منخفض',
                        default    => $state,
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('detected_at')
                    ->label('تاريخ الاكتشاف')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pattern_type')
                    ->label('النمط')
                    ->options(EmployeePattern::patternTypes()),
                Tables\Filters\SelectFilter::make('risk_level')
                    ->label('الخطر')
                    ->options([
                        'critical' => 'حرج',
                        'high'     => 'عالي',
                        'medium'   => 'متوسط',
                        'low'      => 'منخفض',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('نشط'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('frequency_score', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeePatterns::route('/'),
        ];
    }
}
