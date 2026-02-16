<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollResource\Pages;
use App\Models\Payroll;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class PayrollResource extends Resource
{
    protected static ?string $model = Payroll::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'كشوف الرواتب';

    protected static ?string $modelLabel = 'كشف راتب';

    protected static ?string $pluralModelLabel = 'كشوف الرواتب';

    protected static ?string $navigationGroup = 'المالية';

    protected static ?int $navigationSort = 15;

    /**
     * security_level >= 7 أو is_super_admin فقط.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->is_super_admin || $user->security_level >= 7);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    /**
     * تحديد نطاق البيانات حسب فرع المستخدم — level < 10 يرى فرعه فقط.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['user.branch']);
        $user  = auth()->user();

        if ($user && !$user->is_super_admin && $user->security_level < 10) {
            $query->whereHas('user', fn (Builder $q) => $q->where('branch_id', $user->branch_id));
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الراتب')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('الموظف')
                        ->relationship('user', 'name_ar')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('period')
                        ->label('الفترة')
                        ->placeholder('2025-06')
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->label('الحالة')
                        ->options([
                            'draft'    => 'مسودة',
                            'approved' => 'معتمد',
                            'paid'     => 'مدفوع',
                        ])
                        ->default('draft')
                        ->required(),
                ]),

            Forms\Components\Section::make('الراتب')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('basic_salary')
                        ->label('الراتب الأساسي')
                        ->numeric()
                        ->prefix('ر.س'),
                    Forms\Components\TextInput::make('housing_allowance')
                        ->label('بدل سكن')
                        ->numeric()
                        ->prefix('ر.س'),
                    Forms\Components\TextInput::make('transport_allowance')
                        ->label('بدل نقل')
                        ->numeric()
                        ->prefix('ر.س'),
                    Forms\Components\TextInput::make('gross_salary')
                        ->label('الإجمالي')
                        ->numeric()
                        ->prefix('ر.س')
                        ->disabled(),
                ]),

            Forms\Components\Section::make('الاستقطاعات')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('delay_deductions')
                        ->label('التأخير')
                        ->numeric()
                        ->prefix('ر.س'),
                    Forms\Components\TextInput::make('absence_deductions')
                        ->label('الغياب')
                        ->numeric()
                        ->prefix('ر.س'),
                    Forms\Components\TextInput::make('early_leave_deductions')
                        ->label('خروج مبكر')
                        ->numeric()
                        ->prefix('ر.س'),
                    Forms\Components\TextInput::make('total_deductions')
                        ->label('إجمالي الاستقطاعات')
                        ->numeric()
                        ->prefix('ر.س')
                        ->disabled(),
                ]),

            Forms\Components\Section::make('الإضافات والصافي')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('overtime_pay')
                        ->label('أجر إضافي')
                        ->numeric()
                        ->prefix('ر.س'),
                    Forms\Components\TextInput::make('bonuses')
                        ->label('مكافآت')
                        ->numeric()
                        ->prefix('ر.س'),
                    Forms\Components\TextInput::make('net_salary')
                        ->label('صافي الراتب')
                        ->numeric()
                        ->prefix('ر.س')
                        ->disabled(),
                ]),

            Forms\Components\Textarea::make('notes')
                ->label('ملاحظات')
                ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('user.branch.name_ar')
                    ->label('الفرع')
                    ->sortable(),

                Tables\Columns\TextColumn::make('period')
                    ->label('الفترة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gross_salary')
                    ->label('الإجمالي')
                    ->money('SAR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_deductions')
                    ->label('الاستقطاعات')
                    ->money('SAR')
                    ->color('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('net_salary')
                    ->label('الصافي')
                    ->money('SAR')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('present_days')
                    ->label('حضور')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('absent_days')
                    ->label('غياب')
                    ->alignCenter()
                    ->color('danger'),

                Tables\Columns\TextColumn::make('late_days')
                    ->label('تأخير')
                    ->alignCenter()
                    ->color('warning'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'approved',
                        'primary' => 'paid',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'draft'    => 'مسودة',
                        'approved' => 'معتمد',
                        'paid'     => 'مدفوع',
                        default    => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'draft'    => 'مسودة',
                        'approved' => 'معتمد',
                        'paid'     => 'مدفوع',
                    ]),
                Tables\Filters\SelectFilter::make('branch')
                    ->label('الفرع')
                    ->relationship('branch', 'name_ar'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('approve')
                    ->label('اعتماد')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Payroll $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (Payroll $record) {
                        $record->update([
                            'status'      => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);
                        Notification::make()
                            ->title('تم اعتماد كشف الراتب')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayrolls::route('/'),
            'create' => Pages\CreatePayroll::route('/create'),
            'edit'   => Pages\EditPayroll::route('/{record}/edit'),
        ];
    }
}
