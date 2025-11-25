<?php

namespace App\Services;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function getDailyData(Carbon $date): array
    {
        $reservations = Reservation::whereDate('start_time', $date)
            ->whereIn('status', ['confirmed', 'completed'])
            ->get();

        return $this->aggregateData($reservations, 'Daily Report: ' . $date->format('d M Y'));
    }

    public function getMonthlyData(int $month, int $year): array
    {
        $reservations = Reservation::whereMonth('start_time', $month)
            ->whereYear('start_time', $year)
            ->whereIn('status', ['confirmed', 'completed'])
            ->get();

        $date = Carbon::createFromDate($year, $month, 1);
        return $this->aggregateData($reservations, 'Monthly Report: ' . $date->format('F Y'));
    }

    public function getYearlyData(int $year): array
    {
        $reservations = Reservation::whereYear('start_time', $year)
            ->whereIn('status', ['confirmed', 'completed'])
            ->get();

        return $this->aggregateData($reservations, 'Yearly Report: ' . $year);
    }

    private function aggregateData(Collection $reservations, string $title): array
    {
        $totalRevenue = $reservations->sum('total_price');
        $totalReservations = $reservations->count();
        $totalHours = $reservations->sum('duration_hours');
        
        // Group by court
        $courtStats = $reservations->groupBy('padel_court.name')->map(function ($group) {
            return [
                'count' => $group->count(),
                'revenue' => $group->sum('total_price'),
                'hours' => $group->sum('duration_hours'),
            ];
        });

        return [
            'title' => $title,
            'total_revenue' => $totalRevenue,
            'total_reservations' => $totalReservations,
            'total_hours' => $totalHours,
            'court_stats' => $courtStats,
            'reservations' => $reservations,
            'generated_at' => now()->format('d M Y H:i'),
        ];
    }
}
