<?php

namespace App\Filament\Resources\ReportFormulaResource\Pages;

use App\Filament\Resources\ReportFormulaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReportFormulas extends ListRecords
{
    protected static string $resource = ReportFormulaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
