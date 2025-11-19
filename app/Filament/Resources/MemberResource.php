<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers\ReservationsRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ReservationResource;

class MemberResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Member Management';

    protected static ?string $modelLabel = 'Member';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Member Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Membership Details')
                    ->schema([
                        Forms\Components\TextInput::make('membership_code')
                            ->label('Membership Code')
                            ->hint('Kosongkan untuk kode otomatis')
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
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
                        Forms\Components\Textarea::make('membership_notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Portal Access')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->minLength(8)
                            ->confirmed()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state): bool => filled($state)),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->same('password')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('membership_code')
                    ->label('Member Code')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('membership_level')
                    ->colors([
                        'secondary' => 'regular',
                        'info' => 'silver',
                        'warning' => 'gold',
                        'success' => 'vip',
                    ])
                    ->label('Level'),
                Tables\Columns\TextColumn::make('membership_expires_at')
                    ->label('Expires')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state && $state < now()->toDateString() ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('membership_level')
                    ->options([
                        'regular' => 'Regular',
                        'silver' => 'Silver',
                        'gold' => 'Gold',
                        'vip' => 'VIP',
                    ]),
                Tables\Filters\Filter::make('active')
                    ->label('Active Members')
                    ->query(fn (Builder $query): Builder => $query->where(function ($query) {
                        $query->whereNull('membership_expires_at')
                            ->orWhere('membership_expires_at', '>=', now()->toDateString());
                    })),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('createReservation')
                        ->label('Create Reservation')
                        ->icon('heroicon-o-calendar')
                        ->url(fn (User $record) => ReservationResource::getUrl('create', ['member' => $record->getKey()])),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ReservationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_admin', false);
    }
}

