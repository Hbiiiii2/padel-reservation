<?php

namespace App\Filament\Widgets;

use App\Models\PadelCourt;
use Filament\Widgets\BarChartWidget;

class ReservationsByCourtChart extends BarChartWidget
{
    protected static ?string $heading = 'Reservations by Court (This Month)';

    protected function getData(): array
    {
        $courts = PadelCourt::withCount(['reservations' => function ($query) {
            $query->whereMonth('created_at', now()->month);
        }])->get();

        return [
            'datasets' => [
                [
                    'label' => 'Reservations This Month',
                    'data' => $courts->pluck('reservations_count')->toArray(),
                    'backgroundColor' => ['#3B82F6', '#10B981', '#EF4444', '#F59E0B', '#8B5CF6'],
                ],
            ],
            'labels' => $courts->pluck('name')->toArray(),
        ];
    }
}

