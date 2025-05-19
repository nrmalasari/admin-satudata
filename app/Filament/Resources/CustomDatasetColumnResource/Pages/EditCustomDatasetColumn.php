<?php

namespace App\Filament\Resources\CustomDatasetColumnResource\Pages;

use App\Filament\Resources\CustomDatasetColumnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomDatasetColumn extends EditRecord
{
    protected static string $resource = CustomDatasetColumnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
