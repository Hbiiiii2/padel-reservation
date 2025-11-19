<?php

namespace App\Filament\Resources\MemberResource\RelationManagers;

use App\Filament\Resources\ReservationResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'reservations';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reservation_code')
            ->columns([
                Tables\Columns\TextColumn::make('reservation_code')
                    ->label('Reservation Code')
                    ->copyable(),
                Tables\Columns\TextColumn::make('padelCourt.name')
                    ->label('Court')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime('d M Y H:i')
                    ->label('Start'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'gray',
                        'no_show' => 'orange',
                        default => 'secondary',
                    }),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR')
                    ->label('Total'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('createReservation')
                    ->label('Create Reservation')
                    ->icon('heroicon-o-calendar')
                    ->url(fn () => ReservationResource::getUrl('create', ['member' => $this->ownerRecord->getKey()]))
                    ->openUrlInNewTab(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record) => ReservationResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Belum ada reservasi')
            ->emptyStateDescription('Tambahkan reservasi setelah member terdaftar.')
            ->emptyStateActions([
                Tables\Actions\Action::make('createReservation')
                    ->label('Create Reservation')
                    ->icon('heroicon-o-calendar')
                    ->url(fn () => ReservationResource::getUrl('create', ['member' => $this->ownerRecord->getKey()])),
            ]);
    }
}

