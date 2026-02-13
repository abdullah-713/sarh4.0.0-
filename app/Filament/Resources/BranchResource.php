<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('branches.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('branches.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('branches.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('branches.plural_model_label');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // ── Section 1: Branch Identity ────────────────────────
            Forms\Components\Section::make(__('branches.identity_section'))
                ->schema([
                    Forms\Components\TextInput::make('name_ar')
                        ->label(__('branches.name_ar'))
                        ->required()
                        ->maxLength(255)
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.name_ar_hint')),

                    Forms\Components\TextInput::make('name_en')
                        ->label(__('branches.name_en'))
                        ->required()
                        ->maxLength(255)
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.name_en_hint')),

                    Forms\Components\TextInput::make('code')
                        ->label(__('branches.code'))
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20)
                        ->placeholder('مثال: RYD-01')
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.code_hint')),

                    Forms\Components\TextInput::make('phone')
                        ->label(__('branches.phone'))
                        ->tel()
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.phone_hint')),

                    Forms\Components\TextInput::make('email')
                        ->label(__('branches.email'))
                        ->email()
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.email_hint')),

                    Forms\Components\Toggle::make('is_active')
                        ->label(__('branches.is_active'))
                        ->default(true)
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.is_active_hint'))
                        ->helperText('عند إيقافه لن يظهر الفرع في قوائم الاختيار'),
                ])->columns(['default' => 1, 'lg' => 2]),

            // ── Section 2: Geolocation — Leaflet Map Picker ──────
            Forms\Components\Section::make(__('branches.geolocation_section'))
                ->description(__('branches.geolocation_description'))
                ->schema([
                    Forms\Components\ViewField::make('map_picker')
                        ->label(__('branches.map_picker'))
                        ->view('filament.forms.components.map-picker')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('latitude')
                        ->label(__('branches.latitude'))
                        ->required()
                        ->numeric()
                        ->step(0.0000001)
                        ->minValue(-90)
                        ->maxValue(90)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('latitude', $state))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.latitude_hint')),

                    Forms\Components\TextInput::make('longitude')
                        ->label(__('branches.longitude'))
                        ->required()
                        ->numeric()
                        ->step(0.0000001)
                        ->minValue(-180)
                        ->maxValue(180)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('longitude', $state))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.longitude_hint')),

                    Forms\Components\TextInput::make('geofence_radius')
                        ->label(__('branches.geofence_radius'))
                        ->required()
                        ->numeric()
                        ->default(100)
                        ->minValue(1)
                        ->maxValue(100000)
                        ->suffix(__('branches.meters'))
                        ->helperText(__('branches.geofence_radius_help'))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.geofence_radius_hint')),
                ])->columns(['default' => 1, 'lg' => 2]),

            // ── Section 3: Shift & Policy ─────────────────────────
            Forms\Components\Section::make(__('branches.shift_section'))
                ->collapsible()
                ->collapsed()
                ->schema([
                    Forms\Components\TimePicker::make('default_shift_start')
                        ->label(__('branches.shift_start'))
                        ->default('08:00')
                        ->seconds(false)
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.shift_start_hint')),

                    Forms\Components\TimePicker::make('default_shift_end')
                        ->label(__('branches.shift_end'))
                        ->default('17:00')
                        ->seconds(false)
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.shift_end_hint')),

                    Forms\Components\TextInput::make('grace_period_minutes')
                        ->label(__('branches.grace_period'))
                        ->numeric()
                        ->default(15)
                        ->minValue(0)
                        ->maxValue(120)
                        ->suffix(__('branches.minutes'))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.grace_period_hint')),
                ])->columns(['default' => 1, 'lg' => 3]),

            // ── Section 4: Address (Optional) ─────────────────────
            Forms\Components\Section::make(__('branches.address_section'))
                ->collapsible()
                ->collapsed()
                ->schema([
                    Forms\Components\Textarea::make('address_ar')
                        ->label(__('branches.address_ar'))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.address_ar_hint')),

                    Forms\Components\Textarea::make('address_en')
                        ->label(__('branches.address_en'))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.address_en_hint')),

                    Forms\Components\TextInput::make('city_ar')
                        ->label(__('branches.city_ar'))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.city_ar_hint')),

                    Forms\Components\TextInput::make('city_en')
                        ->label(__('branches.city_en'))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.city_en_hint')),
                ])->columns(['default' => 1, 'lg' => 2]),

            // ── Section 5: Financial (Optional) ───────────────────
            Forms\Components\Section::make(__('branches.financial_section'))
                ->collapsible()
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('monthly_salary_budget')
                        ->label(__('branches.salary_budget'))
                        ->numeric()
                        ->default(0)
                        ->prefix(__('branches.currency_sar'))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.salary_budget_hint')),

                    Forms\Components\TextInput::make('monthly_delay_losses')
                        ->label(__('branches.delay_losses'))
                        ->numeric()
                        ->default(0)
                        ->prefix(__('branches.currency_sar'))
                        ->disabled()
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.delay_losses_hint')),
                ])->columns(['default' => 1, 'lg' => 2]),

            // ── Section 6: الملخص المالي والتشغيلي (Summary) ──────
            Forms\Components\Section::make(__('branches.financial_summary_section'))
                ->description(__('branches.financial_summary_description'))
                ->icon('heroicon-o-calculator')
                ->collapsible()
                ->schema([
                    Forms\Components\Placeholder::make('active_employees_count')
                        ->label(__('branches.active_employees_count'))
                        ->content(fn (?Branch $record): string =>
                            $record?->users()->where('status', 'active')->count() . ' ' . __('branches.employee_unit')
                        )
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.active_employees_count_hint')),

                    Forms\Components\Placeholder::make('total_salaries_sum')
                        ->label(__('branches.total_salaries_sum'))
                        ->content(fn (?Branch $record): string =>
                            number_format((float) ($record?->users()->where('status', 'active')->sum('basic_salary') ?? 0), 2) . ' ' . __('branches.currency_sar')
                        )
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.total_salaries_sum_hint')),

                    Forms\Components\Placeholder::make('branch_vpm')
                        ->label(__('branches.branch_vpm'))
                        ->content(function (?Branch $record): string {
                            if (!$record || !$record->monthly_salary_budget) {
                                return '0.0000 ' . __('branches.currency_sar') . '/' . __('branches.minute_unit');
                            }
                            $budget = (float) $record->monthly_salary_budget;
                            $employeeCount = $record->users()->where('status', 'active')->count();
                            if ($employeeCount === 0) {
                                return '0.0000 ' . __('branches.currency_sar') . '/' . __('branches.minute_unit');
                            }
                            // Working days & hours from branch shift settings
                            $workingDays = 26;
                            $hoursPerDay = 8;
                            if ($record->default_shift_start && $record->default_shift_end) {
                                $s = Carbon::parse($record->default_shift_start);
                                $e = Carbon::parse($record->default_shift_end);
                                if ($e->lt($s)) $e->addDay();
                                $hoursPerDay = $s->diffInMinutes($e) / 60;
                            }
                            $totalMinutes = $employeeCount * $workingDays * $hoursPerDay * 60;
                            $vpm = $totalMinutes > 0 ? $budget / $totalMinutes : 0;
                            return number_format($vpm, 4) . ' ' . __('branches.currency_sar') . '/' . __('branches.minute_unit');
                        })
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.branch_vpm_hint')),

                    Forms\Components\Placeholder::make('monthly_loss_rate')
                        ->label(__('branches.monthly_loss_rate'))
                        ->content(function (?Branch $record): string {
                            $budget = (float) ($record?->monthly_salary_budget ?? 0);
                            $losses = (float) ($record?->monthly_delay_losses ?? 0);
                            if ($budget <= 0) return '0.0%';
                            return number_format(($losses / $budget) * 100, 1) . '%';
                        })
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('branches.monthly_loss_rate_hint')),
                ])->columns(['default' => 1, 'lg' => 2]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label(__('branches.code'))
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name_ar')
                    ->label(__('branches.name_ar'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name_en')
                    ->label(__('branches.name_en'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('city_ar')
                    ->label(__('branches.city_ar'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('geofence_radius')
                    ->label(__('branches.geofence_radius'))
                    ->suffix(' ' . __('branches.meters'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('default_shift_start')
                    ->label(__('branches.shift_start'))
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('default_shift_end')
                    ->label(__('branches.shift_end'))
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('grace_period_minutes')
                    ->label(__('branches.grace_period'))
                    ->suffix(' ' . __('branches.minutes'))
                    ->numeric(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label(__('branches.employees_count'))
                    ->counts('users')
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('branches.is_active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('branches.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('branches.is_active')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('bulk_update_geofence')
                        ->label(__('branches.bulk_update_geofence'))
                        ->icon('heroicon-o-map-pin')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('geofence_radius')
                                ->label(__('branches.geofence_radius'))
                                ->required()
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100000)
                                ->suffix(__('branches.meters'))
                                ->default(100),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(fn (Branch $branch) =>
                                $branch->update(['geofence_radius' => $data['geofence_radius']])
                            );
                            Notification::make()
                                ->title(__('branches.bulk_geofence_updated'))
                                ->body(__('branches.bulk_geofence_updated_body', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('bulk_change_shift')
                        ->label(__('branches.bulk_change_shift'))
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->form([
                            Forms\Components\TimePicker::make('default_shift_start')
                                ->label(__('branches.shift_start'))
                                ->required()
                                ->seconds(false)
                                ->default('08:00'),
                            Forms\Components\TimePicker::make('default_shift_end')
                                ->label(__('branches.shift_end'))
                                ->required()
                                ->seconds(false)
                                ->default('17:00'),
                            Forms\Components\TextInput::make('grace_period_minutes')
                                ->label(__('branches.grace_period'))
                                ->numeric()
                                ->default(15)
                                ->minValue(0)
                                ->maxValue(120)
                                ->suffix(__('branches.minutes')),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $update = ['default_shift_start' => $data['default_shift_start'], 'default_shift_end' => $data['default_shift_end']];
                            if (isset($data['grace_period_minutes'])) {
                                $update['grace_period_minutes'] = $data['grace_period_minutes'];
                            }
                            $records->each(fn (Branch $branch) => $branch->update($update));
                            Notification::make()
                                ->title(__('branches.bulk_shift_updated'))
                                ->body(__('branches.bulk_shift_updated_body', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('code');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'view'   => Pages\ViewBranch::route('/{record}'),
            'edit'   => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
