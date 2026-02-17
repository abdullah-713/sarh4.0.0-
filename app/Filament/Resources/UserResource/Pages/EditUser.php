<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Jobs\RecalculateMonthlyAttendanceJob;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Override to handle non-fillable fields (security_level, is_super_admin).
     * These are excluded from $fillable for safety but must be settable by admins.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Extract non-fillable fields
        $securityLevel = $data['security_level'] ?? null;
        $isSuperAdmin = $data['is_super_admin'] ?? null;
        unset($data['security_level'], $data['is_super_admin']);

        // Normal fillable update
        $record->update($data);

        // Force-set non-fillable fields if the current user has permission
        $currentUser = auth()->user();
        if ($currentUser?->is_super_admin || $currentUser?->security_level >= 10) {
            if ($securityLevel !== null) {
                $record->forceFill(['security_level' => (int) $securityLevel])->save();
            }
            if ($isSuperAdmin !== null) {
                $record->forceFill(['is_super_admin' => (bool) $isSuperAdmin])->save();
            }
        }

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('recalculate_attendance')
                ->label(__('users.recalc_action'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading(__('users.recalc_modal_heading'))
                ->modalDescription(__('users.recalc_modal_description'))
                ->modalSubmitActionLabel(__('users.recalc_confirm'))
                ->action(function () {
                    RecalculateMonthlyAttendanceJob::dispatch(
                        'user',
                        $this->record->id,
                        auth()->id(),
                    );

                    Notification::make()
                        ->title(__('users.recalc_dispatched'))
                        ->icon('heroicon-o-arrow-path')
                        ->success()
                        ->send();
                }),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
