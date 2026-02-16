<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'الهيكل التنظيمي';
    }

    public static function getNavigationLabel(): string
    {
        return 'الأقسام';
    }

    public static function getModelLabel(): string
    {
        return 'قسم';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الأقسام';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('بيانات القسم')
                ->icon('heroicon-o-building-office')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name_ar')
                        ->label('الاسم (عربي)')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('name_en')
                        ->label('الاسم (English)')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('code')
                        ->label('رمز القسم')
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),

                    Forms\Components\Select::make('branch_id')
                        ->label('الفرع')
                        ->options(Branch::pluck('name_ar', 'id'))
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('parent_id')
                        ->label('القسم الأعلى')
                        ->options(Department::pluck('name_ar', 'id'))
                        ->searchable()
                        ->placeholder('بدون (قسم رئيسي)'),

                    Forms\Components\Select::make('head_id')
                        ->label('رئيس القسم')
                        ->options(User::pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('لم يُحدد'),
                ]),

            Forms\Components\Section::make('الوصف')
                ->icon('heroicon-o-document-text')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Forms\Components\Textarea::make('description_ar')
                        ->label('الوصف (عربي)')
                        ->rows(3),

                    Forms\Components\Textarea::make('description_en')
                        ->label('الوصف (English)')
                        ->rows(3),

                    Forms\Components\Toggle::make('is_active')
                        ->label('مفعّل')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_ar')
                    ->label('القسم')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('الرمز')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('branch.name_ar')
                    ->label('الفرع')
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name_ar')
                    ->label('القسم الأعلى')
                    ->default('—'),

                Tables\Columns\TextColumn::make('head.name')
                    ->label('الرئيس')
                    ->default('—'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('الموظفون')
                    ->counts('users')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),
            ])
            ->defaultSort('branch_id')
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->options(Branch::pluck('name_ar', 'id')),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->trueLabel('مفعّل')
                    ->falseLabel('معطّل'),
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
            'index'  => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit'   => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
