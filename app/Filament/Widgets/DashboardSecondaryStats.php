<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardSecondaryStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending Reservations', Reservation::where('status', 'pending')->count())
                ->description('Awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Confirmed Today', Reservation::where('status', 'confirmed')->whereDate('start_time', today())->count())
                ->description('Confirmed for today')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Total Customers', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
            Stat::make('Cancellation Rate', $this->getCancellationRate() . '%')
                ->description('This month cancellations')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }

    private function getCancellationRate(): float
    {
        $totalThisMonth = Reservation::whereMonth('created_at', now()->month)->count();
        $cancelledThisMonth = Reservation::where('status', 'cancelled')
            ->whereMonth('created_at', now()->month)
            ->count();

        if ($totalThisMonth === 0) {
            return 0;
        }

        return round(($cancelledThisMonth / $totalThisMonth) * 100, 1);
    }
}

