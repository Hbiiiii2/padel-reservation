<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class ReservationCalendarWidget extends FullCalendarWidget
{
    public Model | int | string | null $record = null;
    public Model | string | null $model = Reservation::class;

    protected int | string | array $columnSpan = 'full';

    protected function headerActions(): array
    {
        return [];
    }

    protected function modalActions(): array
    {
        return [];
    }

    protected function viewAction(): Actions\ViewAction
    {
        return Actions\ViewAction::make()
            ->hidden();
    }

    public function fetchEvents(array $info): array
    {
        $start = Carbon::parse($info['start']);
        $end = Carbon::parse($info['end']);

        return Reservation::with(['padelCourt', 'user'])
            ->whereBetween('start_time', [$start, $end])
            ->get()
            ->map(function (Reservation $reservation) {
                return [
                    'id' => (string) $reservation->id,
                    'title' => "{$reservation->padelCourt->name} - {$reservation->user->name}",
                    'start' => $reservation->start_time->toIso8601String(),
                    'end' => $reservation->end_time->toIso8601String(),
                    'color' => $this->getStatusColor($reservation->status),
                    'extendedProps' => [
                        'reservation_code' => $reservation->reservation_code,
                        'status' => $reservation->status,
                        'court' => $reservation->padelCourt->name,
                        'customer' => $reservation->user->name,
                        'time_slot' => $reservation->start_time->format('d M Y H:i') . ' - ' . $reservation->end_time->format('H:i'),
                    ],
                ];
            })
            ->toArray();
    }

    protected function getStatusColor(string $status): string
    {
        return match ($status) {
            'confirmed' => '#10B981',
            'pending' => '#F59E0B',
            'cancelled' => '#EF4444',
            'completed' => '#6B7280',
            'no_show' => '#DC2626',
            default => '#6B7280',
        };
    }
}

