<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Filament\Resources\ReservationResource\RelationManagers;
use App\Models\Reservation;
use App\Models\PadelCourt;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Reservation Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Reservation Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih Customer')
                            ->default(fn () => request()->integer('member'))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->required()
                                    ->email()
                                    ->maxLength(255)
                                    ->unique(),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(20),
                                Forms\Components\Textarea::make('address')
                                    ->rows(3),
                                Forms\Components\TextInput::make('membership_code')
                                    ->label('Membership Code')
                                    ->maxLength(20)
                                    ->hint('Kosongkan untuk otomatis'),
                                Forms\Components\Select::make('membership_level')
                                    ->label('Membership Level')
                                    ->options([
                                        'regular' => 'Regular',
                                        'silver' => 'Silver',
                                        'gold' => 'Gold',
                                        'vip' => 'VIP',
                                    ])
                                    ->default('regular')
                                    ->required(),
                                Forms\Components\DatePicker::make('membership_starts_at')
                                    ->label('Start Date'),
                                Forms\Components\DatePicker::make('membership_expires_at')
                                    ->label('Expiry Date')
                                    ->minDate(fn (Get $get) => $get('membership_starts_at')),
                                Forms\Components\TextInput::make('password')
                                    ->required()
                                    ->password()
                                    ->minLength(8)
                                    ->confirmed(),
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->required()
                                    ->password()
                                    ->minLength(8),
                                Forms\Components\Textarea::make('membership_notes')
                                    ->label('Notes')
                                    ->rows(3),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $data['password'] = bcrypt($data['password']);
                                $data['is_admin'] = false;
                                $data['membership_code'] = $data['membership_code'] ?: User::generateMembershipCode();
                                unset($data['password_confirmation']);
                                return User::create($data);
                            }),
                        Forms\Components\Select::make('padel_court_id')
                            ->label('Padel Court')
                            ->options(PadelCourt::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $court = PadelCourt::find($state);
                                    $set('price_per_hour', $court->price_per_hour);
                                }
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Booking Time')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_time')
                            ->required()
                            ->minDate(now())
                            ->seconds(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $endTime = $get('end_time');
                                if ($state && $endTime) {
                                    $duration = \Carbon\Carbon::parse($state)->diffInHours($endTime);
                                    $set('duration_hours', $duration);
                                    $set('total_price', $duration * $get('price_per_hour') + $get('equipment_fee'));
                                }
                            }),
                        Forms\Components\DateTimePicker::make('end_time')
                            ->required()
                            ->minDate(now())
                            ->seconds(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $startTime = $get('start_time');
                                if ($state && $startTime) {
                                    $duration = \Carbon\Carbon::parse($startTime)->diffInHours($state);
                                    $set('duration_hours', $duration);
                                    $set('total_price', $duration * $get('price_per_hour') + $get('equipment_fee'));
                                }
                            }),
                        Forms\Components\TextInput::make('duration_hours')
                            ->label('Duration (hours)')
                            ->numeric()
                            ->readOnly()
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price_per_hour')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),
                        Forms\Components\Toggle::make('equipment_rental')
                            ->label('Include Equipment Rental')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $equipmentFee = $state ? 50000 : 0;
                                $set('equipment_fee', $equipmentFee);
                                $duration = $get('duration_hours') ?? 1;
                                $set('total_price', $duration * $get('price_per_hour') + $equipmentFee);
                            }),
                        Forms\Components\TextInput::make('equipment_fee')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->readOnly(),
                        Forms\Components\TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'cancelled' => 'Cancelled',
                                'completed' => 'Completed',
                                'no_show' => 'No Show',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\TextInput::make('number_of_players')
                            ->numeric()
                            ->minValue(2)
                            ->maxValue(8)
                            ->default(2),
                        Forms\Components\TextInput::make('player_names')
                            ->label('Player Names (comma separated)')
                            ->maxLength(500),
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reservation_code')
                    ->searchable()
                    ->copyable()
                    ->label('Reservation Code'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->label('Customer'),
                Tables\Columns\TextColumn::make('user.membership_code')
                    ->label('Member Code')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('padelCourt.name')
                    ->searchable()
                    ->sortable()
                    ->label('Court'),
                Tables\Columns\TextColumn::make('start_time')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->label('Start Time'),
                Tables\Columns\TextColumn::make('end_time')
                    ->dateTime('H:i')
                    ->sortable()
                    ->label('End Time'),
                Tables\Columns\TextColumn::make('duration_hours')
                    ->suffix(' hours')
                    ->sortable()
                    ->label('Duration'),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('IDR')
                    ->sortable()
                    ->label('Total Price'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        'completed' => 'gray',
                        'no_show' => 'orange',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                        'no_show' => 'No Show',
                    ]),
                Tables\Filters\SelectFilter::make('padel_court_id')
                    ->label('Padel Court')
                    ->options(PadelCourt::where('is_active', true)->pluck('name', 'id')),
                Tables\Filters\Filter::make('start_time')
                    ->form([
                        Forms\Components\DatePicker::make('start_from'),
                        Forms\Components\DatePicker::make('start_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_time', '>=', $date),
                            )
                            ->when(
                                $data['start_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_time', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('upcoming')
                    ->label('Upcoming Reservations')
                    ->query(fn (Builder $query): Builder => $query->where('start_time', '>=', now())),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
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
                        ->visible(fn (Reservation $record) => $record->status === 'pending'),
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
                        ->visible(fn (Reservation $record) => in_array($record->status, ['pending', 'confirmed'])),
                    Tables\Actions\Action::make('complete')
                        ->action(function (Reservation $record) {
                            $record->update(['status' => 'completed']);
                            Notification::make()
                                ->title('Reservation Completed')
                                ->body("Reservation {$record->reservation_code} has been marked as completed.")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->color('gray')
                        ->icon('heroicon-o-check-badge')
                        ->visible(fn (Reservation $record) => $record->status === 'confirmed'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('confirmSelected')
                        ->action(fn ($records) => $records->each->update(['status' => 'confirmed']))
                        ->requiresConfirmation()
                        ->color('success')
                        ->icon('heroicon-o-check'),
                    Tables\Actions\BulkAction::make('cancelSelected')
                        ->action(fn ($records) => $records->each->update(['status' => 'cancelled']))
                        ->requiresConfirmation()
                        ->color('danger')
                        ->icon('heroicon-o-x-mark'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('start_time', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}