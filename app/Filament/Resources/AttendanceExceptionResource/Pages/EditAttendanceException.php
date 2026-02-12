<?php

namespace App\Filament\Resources\AttendanceExceptionResource\Pages;

use App\Filament\Resources\AttendanceExceptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceException extends EditRecord
{
    protected static string $resource = AttendanceExceptionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
