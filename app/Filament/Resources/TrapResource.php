<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrapResource\Pages;
use App\Models\Trap;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TrapResource extends Resource
{
    protected static ?string $model = Trap::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'الأمان';

    protected static ?string $navigationLabel = 'الفخاخ النفسية';

    protected static ?string $modelLabel = 'فخ';

    protected static ?string $pluralModelLabel = 'الفخاخ';

    protected static ?int $navigationSort = 90;

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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('إعدادات الفخ')
                ->schema([
                    Forms\Components\TextInput::make('trap_code')
                        ->label('رمز الفخ')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->helperText('رمز فريد مثل SALARY_PEEK, EDIT_ATTENDANCE'),

                    Forms\Components\TextInput::make('name')
                        ->label('الاسم')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Textarea::make('description')
                        ->label('الوصف')
                        ->rows(3),

                    Forms\Components\Select::make('trigger_type')
                        ->label('نوع التفعيل')
                        ->options([
                            'button_click' => 'نقر زر',
                            'page_visit'   => 'زيارة صفحة',
                            'form_submit'  => 'إرسال نموذج',
                            'data_export'  => 'تصدير بيانات',
                        ])
                        ->default('button_click')
                        ->required(),

                    Forms\Components\TextInput::make('risk_weight')
                        ->label('وزن الخطر')
                        ->numeric()
                        ->minValue(0.1)
                        ->maxValue(5.0)
                        ->step(0.1)
                        ->default(1.0)
                        ->helperText('1.0 (عادي) إلى 5.0 (حرج)'),

                    Forms\Components\Select::make('placement')
                        ->label('موقع العرض')
                        ->options([
                            'sidebar'   => 'الشريط الجانبي',
                            'dashboard' => 'لوحة التحكم',
                            'settings'  => 'الإعدادات',
                            'toolbar'   => 'شريط الأدوات',
                        ])
                        ->default('sidebar'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('نشط')
                        ->default(true),
                ])->columns(2),

            Forms\Components\Section::make('الاستهداف')
                ->schema([
                    Forms\Components\CheckboxList::make('target_levels')
                        ->label('المستويات المستهدفة')
                        ->options([
                            1 => 'مستوى 1 — موظف جديد',
                            2 => 'مستوى 2 — موظف',
                            3 => 'مستوى 3 — موظف أول',
                            4 => 'مستوى 4 — رئيس قسم',
                            5 => 'مستوى 5 — مشرف',
                            6 => 'مستوى 6 — مدير فرع',
                            7 => 'مستوى 7 — مدير موارد بشرية',
                            8 => 'مستوى 8 — مدير HR أول',
                            9 => 'مستوى 9 — مدير تنفيذي',
                        ])
                        ->helperText('اتركه فارغاً لاستهداف جميع المستويات')
                        ->columns(3),
                ]),

            Forms\Components\Section::make('الاستجابة الوهمية')
                ->schema([
                    Forms\Components\KeyValue::make('fake_response')
                        ->label('الاستجابة الوهمية (JSON)')
                        ->keyLabel('المفتاح')
                        ->valueLabel('القيمة')
                        ->default(['status' => 'success', 'message' => 'تم تنفيذ العملية بنجاح']),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('trap_code')
                    ->label('الرمز')
                    ->badge()
                    ->color('danger')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('trigger_type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'button_click' => 'نقر زر',
                        'page_visit'   => 'زيارة صفحة',
                        'form_submit'  => 'إرسال نموذج',
                        'data_export'  => 'تصدير بيانات',
                        default        => $state,
                    }),

                Tables\Columns\TextColumn::make('risk_weight')
                    ->label('وزن الخطر')
                    ->badge()
                    ->color(fn (float $state): string => match (true) {
                        $state >= 4.0 => 'danger',
                        $state >= 2.5 => 'warning',
                        default       => 'success',
                    }),

                Tables\Columns\TextColumn::make('placement')
                    ->label('الموقع')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sidebar'   => 'شريط جانبي',
                        'dashboard' => 'لوحة تحكم',
                        'settings'  => 'إعدادات',
                        'toolbar'   => 'شريط أدوات',
                        default     => $state,
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('interactions_count')
                    ->label('التفاعلات')
                    ->counts('interactions')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trigger_type')
                    ->label('نوع التفعيل')
                    ->options([
                        'button_click' => 'نقر زر',
                        'page_visit'   => 'زيارة صفحة',
                        'form_submit'  => 'إرسال نموذج',
                        'data_export'  => 'تصدير بيانات',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTraps::route('/'),
            'create' => Pages\CreateTrap::route('/create'),
            'edit'   => Pages\EditTrap::route('/{record}/edit'),
        ];
    }
}
