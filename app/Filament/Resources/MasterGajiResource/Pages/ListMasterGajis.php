<?php

namespace App\Filament\Resources\MasterGajiResource\Pages;

use App\Filament\Resources\MasterGajiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterGajis extends ListRecords
{
    protected static string $resource = MasterGajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
