<?php

namespace App\Filament\Resources\CustomDatasetRowResource\Pages;

use App\Filament\Resources\CustomDatasetRowResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomDatasetRow extends EditRecord
{
    protected static string $resource = CustomDatasetRowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
