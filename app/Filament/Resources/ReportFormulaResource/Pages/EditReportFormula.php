<?php

namespace App\Filament\Resources\ReportFormulaResource\Pages;

use App\Filament\Resources\ReportFormulaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReportFormula extends EditRecord
{
    protected static string $resource = ReportFormulaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
