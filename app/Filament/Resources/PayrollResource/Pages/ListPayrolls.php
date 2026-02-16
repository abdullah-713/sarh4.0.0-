<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Log;

class ListPayrolls extends ListRecords
{
    protected static string $resource = PayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generatePayroll')
                ->label('توليد كشوف الرواتب')
                ->icon('heroicon-o-calculator')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\TextInput::make('period')
                        ->label('الفترة')
                        ->placeholder('2025-06')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $period = $data['period'];
                    $users = \App\Models\User::where('status', 'active')->get();
                    $count = 0;
                    $failed = 0;
                    $failedNames = [];

                    foreach ($users as $user) {
                        try {
                            \App\Models\Payroll::generateForUser($user, $period);
                            $count++;
                        } catch (\Throwable $e) {
                            $failed++;
                            $failedNames[] = $user->name_ar ?: $user->name_en;

                            Log::error('فشل توليد كشف راتب', [
                                'user_id'     => $user->id,
                                'employee_id' => $user->employee_id,
                                'name'        => $user->name_ar ?: $user->name_en,
                                'period'      => $period,
                                'error'       => $e->getMessage(),
                                'trace'       => $e->getTraceAsString(),
                            ]);

                            continue;
                        }
                    }

                    \Filament\Notifications\Notification::make()
                        ->title("تم توليد {$count} كشف راتب")
                        ->success()
                        ->send();

                    if ($failed > 0) {
                        \Filament\Notifications\Notification::make()
                            ->title("⚠️ فشل توليد {$failed} كشف راتب")
                            ->body('الموظفون: ' . implode('، ', array_slice($failedNames, 0, 10)) . ($failed > 10 ? '... و' . ($failed - 10) . ' آخرين' : '') . '. راجع سجل الأخطاء للتفاصيل.')
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
}
