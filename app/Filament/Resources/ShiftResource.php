<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 8;

    public static function getNavigationGroup(): ?string
    {
        return 'إدارة الموارد البشرية';
    }

    public static function getNavigationLabel(): string
    {
        return 'الورديات';
    }

    public static function getModelLabel(): string
    {
        return 'وردية';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الورديات';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات الوردية')
                ->icon('heroicon-o-clock')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name_ar')
                        ->label('الاسم (عربي)')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('name_en')
                        ->label('الاسم (English)')
                        ->maxLength(255),

                    Forms\Components\TimePicker::make('start_time')
                        ->label('وقت البدء')
                        ->required()
                        ->seconds(false),

                    Forms\Components\TimePicker::make('end_time')
                        ->label('وقت الانتهاء')
                        ->required()
                        ->seconds(false),

                    Forms\Components\TextInput::make('grace_period_minutes')
                        ->label('فترة السماح (دقائق)')
                        ->numeric()
                        ->default(15)
                        ->minValue(0)
                        ->maxValue(120)
                        ->suffix('دقيقة'),

                    Forms\Components\Toggle::make('is_overnight')
                        ->label('وردية ليلية')
                        ->helperText('تتجاوز منتصف الليل')
                        ->default(false),

                    Forms\Components\Toggle::make('is_active')
                        ->label('مفعّلة')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('الوردية')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('البدء')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('الانتهاء')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('grace_period_minutes')
                    ->label('السماح')
                    ->suffix(' دقيقة')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_overnight')
                    ->label('ليلية')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('مفعّلة')
                    ->boolean(),

                Tables\Columns\TextColumn::make('assignments_count')
                    ->label('الموظفون')
                    ->counts('assignments')
                    ->sortable(),
            ])
            ->defaultSort('name_ar')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('مفعّلة')
                    ->falseLabel('معطّلة'),

                Tables\Filters\TernaryFilter::make('is_overnight')
                    ->label('النوع')
                    ->trueLabel('ليلية')
                    ->falseLabel('نهارية'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index'  => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit'   => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
