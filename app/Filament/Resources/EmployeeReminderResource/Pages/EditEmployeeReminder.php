<?php

namespace App\Filament\Resources\EmployeeReminderResource\Pages;

use App\Filament\Resources\EmployeeReminderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeReminder extends EditRecord
{
    protected static string $resource = EmployeeReminderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
