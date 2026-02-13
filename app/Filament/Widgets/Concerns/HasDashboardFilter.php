<?php

namespace App\Filament\Widgets\Concerns;

use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/**
 * Provides date-range helpers based on the dashboard period filter.
 *
 * Requires InteractsWithPageFilters to be used alongside this trait.
 * All filter accesses are null-safe — handles uninitialized state gracefully.
 */
trait HasDashboardFilter
{
    use InteractsWithPageFilters;

    /**
     * Safely retrieve the filters array (never null).
     */
    protected function safeFilters(): array
    {
        return is_array($this->filters ?? null) ? $this->filters : [];
    }

    /**
     * Convert the dashboard period filter into a [startDate, endDate] pair.
     *
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    protected function getFilterDates(): array
    {
        $filters = $this->safeFilters();
        $period  = $filters['period'] ?? 'today';

        return match ($period) {
            'week'   => [now()->startOfWeek(Carbon::SUNDAY), now()->endOfDay()],
            'month'  => [now()->startOfMonth(), now()->endOfDay()],
            'year'   => [now()->startOfYear(), now()->endOfDay()],
            'custom' => [
                $this->parseDateSafe($filters['start_date'] ?? null)?->startOfDay()
                    ?? now()->startOfDay(),
                $this->parseDateSafe($filters['end_date'] ?? null)?->endOfDay()
                    ?? now()->endOfDay(),
            ],
            default  => [now()->startOfDay(), now()->endOfDay()], // 'today'
        };
    }

    /**
     * Whether the current filter is for a single day.
     */
    protected function isSingleDayFilter(): bool
    {
        $filters = $this->safeFilters();
        $period  = $filters['period'] ?? 'today';

        if ($period === 'today') {
            return true;
        }

        if ($period === 'custom') {
            $start = $this->parseDateSafe($filters['start_date'] ?? null);
            $end   = $this->parseDateSafe($filters['end_date'] ?? null);
            return $start && $end && $start->isSameDay($end);
        }

        return false;
    }

    /**
     * Get a human-readable label for the current period.
     */
    protected function getPeriodLabel(): string
    {
        $filters = $this->safeFilters();
        $period  = $filters['period'] ?? 'today';

        return match ($period) {
            'today'  => __('dashboard.today'),
            'week'   => __('dashboard.this_week'),
            'month'  => __('dashboard.this_month'),
            'year'   => __('dashboard.this_year'),
            'custom' => __('dashboard.custom_range'),
            default  => __('dashboard.today'),
        };
    }

    /**
     * Safely parse a date string into Carbon, returning null on failure.
     */
    protected function parseDateSafe(?string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
