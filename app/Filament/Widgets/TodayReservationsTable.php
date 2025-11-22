<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Reservation;
use Filament\Notifications\Notification;
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
                    ->state(fn(Reservation $record): string => $record->start_time->format('H:i') . ' - ' . $record->end_time->format('H:i')),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                        'gray' => 'completed',
                    ])
                    ->label('Status'),
            ])->actions([
                Tables\Actions\ActionGroup::make([
                
                    Tables\Actions\Action::make('confirm')
                        ->action(function (Reservation $record) {
                            $record->update(['status' => 'confirmed']);
                            Notification::make()
                                ->title('Reservation Confirmed')
                                ->body("Reservation {$record->reservation_code} has been confirmed.")
                                ->success()
                                ->send();
                        
                        })
                        ->requiresConfirmation()
                        ->color('success')
                        ->icon('heroicon-o-check')
                        ->visible(fn(Reservation $record) => $record->status === 'pending'),
                    
                    Tables\Actions\Action::make('cancel')
                        ->action(function (Reservation $record) {
                            $record->update(['status' => 'cancelled']);
                            Notification::make()
                                ->title('Reservation Cancelled')
                                ->body("Reservation {$record->reservation_code} has been cancelled.")
                                ->warning()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->color('danger')
                        ->icon('heroicon-o-x-mark')
                        ->visible(fn(Reservation $record) => in_array($record->status, ['pending', 'confirmed'])),
                    Tables\Actions\Action::make('completed')
                        ->action(function (Reservation $record) {
                            $record->update(['status' => 'completed']);
                            Notification::make()
                                ->title('Reservation Completed')
                                ->body("Reservation {$record->reservation_code} has been marked as completed.")
                                ->warning()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->color('success')
                        ->icon('heroicon-o-check-badge')
                        ->visible(fn(Reservation $record) => in_array($record->status, ['pending', 'confirmed'])),
                ]),
            ])
            ->paginated(false);
    }
}
