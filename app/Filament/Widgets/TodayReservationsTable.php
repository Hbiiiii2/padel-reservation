<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayReservationsTable extends BaseWidget
{
    protected static ?string $heading = "Today's Reservations";

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Reservation::query()
                    ->with(['user', 'padelCourt'])
                    ->whereDate('start_time', today())
                    ->orderBy('start_time')
            )
            ->columns([
                Tables\Columns\TextColumn::make('reservation_code')
                    ->label('Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('padelCourt.name')
                    ->label('Court'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer'),
                Tables\Columns\TextColumn::make('time_slot')
                    ->label('Time Slot')
                    ->state(fn (Reservation $record): string => $record->start_time->format('H:i') . ' - ' . $record->end_time->format('H:i')),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                        'gray' => 'completed',
                    ])
                    ->label('Status'),
            ])
            ->paginated(false);
    }
}

