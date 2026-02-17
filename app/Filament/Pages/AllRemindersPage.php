<?php

namespace App\Filament\Pages;

use App\Models\EmployeeReminder;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AllRemindersPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.pages.all-reminders-page';
    protected static ?string $navigationLabel = 'لوحة التنبيهات';
    protected static ?string $title = 'لوحة التنبيهات الشاملة';
    protected static ?string $navigationGroup = 'الموظفين';
    protected static ?int $navigationSort = 22;

    public static function canAccess(): bool
    {
        return auth()->user()?->security_level >= 10;
    }

    public static function getNavigationBadge(): ?string
    {
        return EmployeeReminder::urgent()->count() + EmployeeReminder::overdue()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return EmployeeReminder::urgent()->count() > 0 ? 'danger' : 'warning';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EmployeeReminder::query()
                    ->with('user')
                    ->where('is_completed', false)
                    ->orderByRaw("
                        CASE 
                            WHEN reminder_date < CURDATE() THEN 0
                            WHEN DATEDIFF(reminder_date, CURDATE()) <= 10 THEN 1
                            WHEN DATEDIFF(reminder_date, CURDATE()) <= 30 THEN 2
                            WHEN DATEDIFF(reminder_date, CURDATE()) <= 90 THEN 3
                            ELSE 4
                        END
                    ")
                    ->orderBy('reminder_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.employee_id')
                    ->label('رقم الموظف')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name_ar')
                    ->label('الموظف')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.branch.name_ar')
                    ->label('الفرع')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reminder_key')
                    ->label('التنبيه')
                    ->searchable()
                    ->icon('heroicon-o-bell')
                    ->iconColor(fn (EmployeeReminder $record): string => $record->status_color),

                Tables\Columns\TextColumn::make('reminder_date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn (EmployeeReminder $record): string => $record->status_color),

                Tables\Columns\TextColumn::make('days_until_due')
                    ->label('الأيام المتبقية')
                    ->formatStateUsing(fn (int $state): string => 
                        $state < 0 ? 'متأخر ' . abs($state) . ' يوم' : $state . ' يوم'
                    )
                    ->badge()
                    ->color(fn (EmployeeReminder $record): string => $record->status_color)
                    ->extraAttributes(fn (EmployeeReminder $record): array => 
                        $record->is_urgent ? ['class' => 'animate-pulse'] : []
                    ),

                Tables\Columns\TextColumn::make('status_label')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (EmployeeReminder $record): string => $record->status_color)
                    ->size('lg')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('priority')
                    ->label('الأولوية')
                    ->options([
                        'overdue' => '🔴 متأخر',
                        'urgent' => '🟠 عاجل (≤10 أيام)',
                        'warning' => '🟡 قريب (≤30 يوم)',
                        'soon' => '🟢 قادم (≤90 يوم)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'overdue' => $query->overdue(),
                            'urgent' => $query->whereDate('reminder_date', '<=', now()->addDays(10))
                                ->whereDate('reminder_date', '>=', now()),
                            'warning' => $query->whereDate('reminder_date', '<=', now()->addDays(30))
                                ->whereDate('reminder_date', '>=', now()),
                            'soon' => $query->whereDate('reminder_date', '<=', now()->addDays(90))
                                ->whereDate('reminder_date', '>=', now()),
                            default => $query,
                        };
                    }),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('الموظف')
                    ->relationship('user', 'name_ar')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('branch')
                    ->label('الفرع')
                    ->relationship('user.branch', 'name_ar')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->label('إكمال')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (EmployeeReminder $record) => $record->markAsCompleted()),

                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn (EmployeeReminder $record): string => $record->reminder_key)
                    ->modalContent(fn (EmployeeReminder $record): \Illuminate\View\View => view('filament.pages.reminder-detail', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_completed')
                    ->label('تحديد كمكتمل')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->markAsCompleted()),
            ])
            ->striped();
    }
}
