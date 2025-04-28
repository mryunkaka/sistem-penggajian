<?php

namespace App\Filament\Resources\MasterGajiResource\Pages;

use App\Filament\Resources\MasterGajiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterGaji extends EditRecord
{
    protected static string $resource = MasterGajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
