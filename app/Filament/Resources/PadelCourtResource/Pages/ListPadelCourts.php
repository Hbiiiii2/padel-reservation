<?php

namespace App\Filament\Resources\PadelCourtResource\Pages;

use App\Filament\Resources\PadelCourtResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPadelCourts extends ListRecords
{
    protected static string $resource = PadelCourtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
