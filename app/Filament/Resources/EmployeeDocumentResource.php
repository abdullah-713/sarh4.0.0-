<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeDocumentResource\Pages;
use App\Models\EmployeeDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeeDocumentResource extends Resource
{
    protected static ?string $model = EmployeeDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'وثائق الموظفين';
    protected static ?string $modelLabel = 'وثيقة';
    protected static ?string $pluralModelLabel = 'الوثائق';
    protected static ?string $navigationGroup = 'الموظفين';
    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الوثيقة')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('الموظف')
                            ->relationship('user', 'name_ar')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('document_type')
                            ->label('نوع الوثيقة')
                            ->options([
                                'passport' => 'جواز سفر',
                                'residence' => 'إقامة',
                                'contract' => 'عقد عمل',
                                'certificate' => 'شهادة',
                                'license' => 'رخصة',
                                'id_card' => 'هوية',
                                'medical' => 'تقرير طبي',
                                'insurance' => 'تأمين',
                                'other' => 'أخرى',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('document_number')
                            ->label('رقم الوثيقة')
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('file_path')
                            ->label('الملف')
                            ->directory('employee-documents')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->previewable()
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('التواريخ')
                    ->schema([
                        Forms\Components\DatePicker::make('issue_date')
                            ->label('تاريخ الإصدار')
                            ->displayFormat('Y-m-d')
                            ->native(false),

                        Forms\Components\DatePicker::make('expiry_date')
                            ->label('تاريخ الانتهاء')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->afterOrEqual('issue_date'),
                    ])
                    ->columns(2),

                Forms\Components\Textarea::make('notes')
                    ->label('ملاحظات')
                    ->rows(3)
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

                Tables\Columns\TextColumn::make('document_type')
                    ->label('نوع الوثيقة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'passport' => 'جواز سفر',
                        'residence' => 'إقامة',
                        'contract' => 'عقد عمل',
                        'certificate' => 'شهادة',
                        'license' => 'رخصة',
                        'id_card' => 'هوية',
                        'medical' => 'تقرير طبي',
                        'insurance' => 'تأمين',
                        default => 'أخرى',
                    }),

                Tables\Columns\TextColumn::make('document_number')
                    ->label('الرقم')
                    ->searchable(),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('تاريخ الإصدار')
                    ->date('Y-m-d')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable()
                    ->color(fn (EmployeeDocument $record): string => $record->status_color),

                Tables\Columns\TextColumn::make('days_until_expiry')
                    ->label('الأيام المتبقية')
                    ->badge()
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : ($state < 0 ? 'منتهي' : $state . ' يوم'))
                    ->color(fn (EmployeeDocument $record): string => $record->status_color),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('نوع الوثيقة')
                    ->options([
                        'passport' => 'جواز سفر',
                        'residence' => 'إقامة',
                        'contract' => 'عقد عمل',
                        'certificate' => 'شهادة',
                        'license' => 'رخصة',
                        'id_card' => 'هوية',
                        'medical' => 'تقرير طبي',
                        'insurance' => 'تأمين',
                        'other' => 'أخرى',
                    ]),

                Tables\Filters\Filter::make('expiring_soon')
                    ->label('قريبة الانتهاء (90 يوم)')
                    ->query(fn (Builder $query): Builder => $query->expiringSoon(90)),

                Tables\Filters\Filter::make('expired')
                    ->label('منتهية')
                    ->query(fn (Builder $query): Builder => $query->expired()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('تحميل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (EmployeeDocument $record): string => $record->file_url)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expiry_date', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeDocuments::route('/'),
            'create' => Pages\CreateEmployeeDocument::route('/create'),
            'edit' => Pages\EditEmployeeDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['user']);

        // المستوى 10 يرى كل شيء
        if (auth()->user()?->security_level >= 10) {
            return $query;
        }

        // المستوى 6+ يرى موظفي فرعه
        if (auth()->user()?->security_level >= 6) {
            return $query->whereHas('user', function (Builder $q) {
                $q->where('branch_id', auth()->user()->branch_id);
            });
        }

        // الباقي يرى وثائقه فقط
        return $query->where('user_id', auth()->id());
    }
}
