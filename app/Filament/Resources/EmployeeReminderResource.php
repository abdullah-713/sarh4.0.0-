<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeReminderResource\Pages;
use App\Models\EmployeeReminder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeReminderResource extends Resource
{
    protected static ?string $model = EmployeeReminder::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'تنبيهات الموظفين';
    protected static ?string $modelLabel = 'تنبيه';
    protected static ?string $pluralModelLabel = 'التنبيهات';
    protected static ?string $navigationGroup = 'الموظفين';
    protected static ?int $navigationSort = 21;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::urgent()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات التنبيه')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('الموظف')
                            ->relationship('user', 'name_ar')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('reminder_key')
                            ->label('المفتاح / العنوان')
                            ->placeholder('مثال: عقد عمل، تجديد إقامة، فحص طبي')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('reminder_date')
                            ->label('تاريخ التنبيه')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_completed')
                            ->label('مكتمل')
                            ->default(false),
                    ])
                    ->columns(2),
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

                Tables\Columns\TextColumn::make('reminder_key')
                    ->label('التنبيه')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('reminder_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable()
                    ->color(fn (EmployeeReminder $record): string => $record->status_color),

                Tables\Columns\TextColumn::make('status_label')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (EmployeeReminder $record): string => $record->status_color)
                    ->extraAttributes(fn (EmployeeReminder $record): array => 
                        $record->is_urgent ? ['class' => 'animate-pulse'] : []
                    ),

                Tables\Columns\IconColumn::make('is_completed')
                    ->label('مكتمل')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_completed')
                    ->label('الحالة')
                    ->options([
                        '0' => 'نشط',
                        '1' => 'مكتمل',
                    ]),

                Tables\Filters\Filter::make('urgent')
                    ->label('عاجل (10 أيام)')
                    ->query(fn (Builder $query): Builder => $query->urgent()),

                Tables\Filters\Filter::make('overdue')
                    ->label('متأخر')
                    ->query(fn (Builder $query): Builder => $query->overdue()),

                Tables\Filters\Filter::make('upcoming')
                    ->label('قادم')
                    ->query(fn (Builder $query): Builder => $query->upcoming()),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label('إكمال')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EmployeeReminder $record): bool => !$record->is_completed)
                    ->requiresConfirmation()
                    ->action(fn (EmployeeReminder $record) => $record->markAsCompleted()),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_completed')
                        ->label('تحديد كمكتمل')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->markAsCompleted()),
                ]),
            ])
            ->defaultSort('reminder_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeReminders::route('/'),
            'create' => Pages\CreateEmployeeReminder::route('/create'),
            'edit' => Pages\EditEmployeeReminder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // المستوى 10 يرى كل شيء
        if (auth()->user()?->security_level >= 10) {
            return $query;
        }

        // المستوى 6+ يرى تنبيهات موظفي فرعه
        if (auth()->user()?->security_level >= 6) {
            return $query->whereHas('user', function (Builder $q) {
                $q->where('branch_id', auth()->user()->branch_id);
            });
        }

        // الباقي يرى تنبيهاته فقط
        return $query->where('user_id', auth()->id());
    }
}
