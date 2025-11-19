<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\PadelCourt;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use RelationManagers\ReservationsRelationManager;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PadelCourtResource\Pages;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use App\Filament\Resources\PadelCourtResource\RelationManagers;

class PadelCourtResource extends Resource
{
    protected static ?string $model = PadelCourt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Court Management';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Court Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Court Details')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'indoor' => 'Indoor',
                                'outdoor' => 'Outdoor',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('price_per_hour')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->minValue(0),
                        Forms\Components\TextInput::make('max_players')
                            ->required()
                            ->numeric()
                            ->minValue(2)
                            ->maxValue(8)
                            ->default(4),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Facilities & Image')
                    ->schema([
                        Forms\Components\TagsInput::make('facilities')
                            ->placeholder('Add facility (press Enter)')
                            ->suggestions([
                                'AC', 'Lighting', 'Locker Room', 'Shower', 
                                'Parking', 'Cafe', 'Pro Shop', 'Training Wall',
                                'Glass Walls', 'Artificial Grass', 'Floodlights'
                            ])
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('image')
                            ->label('Court Image')
                            ->image()
                            ->directory('padel-courts')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Operational Hours')
                    ->schema([
                        Forms\Components\Repeater::make('operational_hours')
                            ->schema([
                                Forms\Components\Select::make('day')
                                    ->options([
                                        'monday' => 'Monday',
                                        'tuesday' => 'Tuesday',
                                        'wednesday' => 'Wednesday',
                                        'thursday' => 'Thursday',
                                        'friday' => 'Friday',
                                        'saturday' => 'Saturday',
                                        'sunday' => 'Sunday',
                                    ])
                                    ->required(),
                                Forms\Components\TimePicker::make('open_time')
                                    ->required()
                                    ->seconds(false),
                                Forms\Components\TimePicker::make('close_time')
                                    ->required()
                                    ->seconds(false),
                            ])
                            ->columns(3)
                            ->defaultItems(7)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'indoor' => 'success',
                        'outdoor' => 'warning',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_hour')
                    ->money('IDR')
                    ->sortable()
                    ->label('Price/Hour'),
                Tables\Columns\TextColumn::make('max_players')
                    ->sortable()
                    ->label('Max Players'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('reservations_count')
                    ->counts('reservations')
                    ->label('Total Bookings')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'indoor' => 'Indoor',
                        'outdoor' => 'Outdoor',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->numeric()
                            ->placeholder('Min Price'),
                        Forms\Components\TextInput::make('max_price')
                            ->numeric()
                            ->placeholder('Max Price'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_hour', '>=', $price),
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('price_per_hour', '<=', $price),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('toggleActive')
                        ->label(fn (PadelCourt $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                        ->color(fn (PadelCourt $record): string => $record->is_active ? 'warning' : 'success')
                        ->icon(fn (PadelCourt $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->action(function (PadelCourt $record): void {
                            $record->update(['is_active' => !$record->is_active]);
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // ReservationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPadelCourts::route('/'),
            'create' => Pages\CreatePadelCourt::route('/create'),
            'edit' => Pages\EditPadelCourt::route('/{record}/edit'),
        ];
    }
}