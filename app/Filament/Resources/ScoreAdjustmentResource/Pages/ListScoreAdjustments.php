<?php

namespace App\Filament\Resources\ScoreAdjustmentResource\Pages;

use App\Filament\Resources\ScoreAdjustmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScoreAdjustments extends ListRecords
{
    protected static string $resource = ScoreAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
