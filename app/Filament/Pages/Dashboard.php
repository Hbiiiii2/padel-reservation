<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardPrimaryStats;
use App\Filament\Widgets\DashboardSecondaryStats;
use App\Filament\Widgets\ReservationsByCourtChart;
use App\Filament\Widgets\ReservationCalendarWidget;
use App\Filament\Widgets\TodayReservationsTable;
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Padel Reservation Dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            DashboardPrimaryStats::class,
            DashboardSecondaryStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TodayReservationsTable::class,
            ReservationCalendarWidget::class,
            ReservationsByCourtChart::class,
        ];
    }
}

