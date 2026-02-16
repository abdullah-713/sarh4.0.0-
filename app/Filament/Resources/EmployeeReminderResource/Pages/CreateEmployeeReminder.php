<?php

namespace App\Filament\Resources\EmployeeReminderResource\Pages;

use App\Filament\Resources\EmployeeReminderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeReminder extends CreateRecord
{
    protected static string $resource = EmployeeReminderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
