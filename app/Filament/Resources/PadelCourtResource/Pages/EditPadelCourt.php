<?php

namespace App\Filament\Resources\PadelCourtResource\Pages;

use App\Filament\Resources\PadelCourtResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPadelCourt extends EditRecord
{
    protected static string $resource = PadelCourtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
