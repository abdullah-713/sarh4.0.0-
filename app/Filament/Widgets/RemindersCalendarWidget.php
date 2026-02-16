<?php

namespace App\Filament\Widgets;

use App\Models\EmployeeReminder;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class RemindersCalendarWidget extends Widget
{
    protected static string $view = 'filament.widgets.reminders-calendar-widget';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    public function getReminders(): Collection
    {
        return EmployeeReminder::with('user')
            ->expiringSoon(90)
            ->limit(50)
            ->get()
            ->map(function (EmployeeReminder $reminder) {
                return [
                    'id' => $reminder->id,
                    'employee' => $reminder->user->name_ar,
                    'key' => $reminder->reminder_key,
                    'date' => $reminder->reminder_date->format('Y-m-d'),
                    'days_until' => $reminder->days_until_due,
                    'status_label' => $reminder->status_label,
                    'status_color' => $reminder->status_color,
                    'is_urgent' => $reminder->is_urgent,
                    'is_overdue' => $reminder->is_overdue,
                ];
            });
    }

    public function getUrgentCount(): int
    {
        return EmployeeReminder::urgent()->count();
    }

    public function getOverdueCount(): int
    {
        return EmployeeReminder::overdue()->count();
    }

    public static function canView(): bool
    {
        return auth()->user()?->security_level >= 6;
    }
}
