<?php

namespace App\Filament\Widgets;

use App\Models\PadelCourt;
use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardPrimaryStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Reservations', Reservation::count())
                ->description('All time bookings')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->getReservationChartData()),
            Stat::make('Today Reservations', Reservation::whereDate('start_time', today())->count())
                ->description('Bookings for today')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            Stat::make('Active Courts', PadelCourt::where('is_active', true)->count())
                ->description('Available for booking')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Monthly Revenue', $this->formatCurrency($this->getMonthlyRevenue()))
                ->description('Current month income')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($this->getRevenueChartData()),
        ];
    }

    private function getReservationChartData(): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = Reservation::whereDate('created_at', $date)->count();
        }

        return $data;
    }

    private function getRevenueChartData(): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = Reservation::where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total_price');
        }

        return $data;
    }

    private function getMonthlyRevenue(): int
    {
        return Reservation::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->sum('total_price');
    }

    private function formatCurrency(int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}

