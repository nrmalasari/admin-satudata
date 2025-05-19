<?php

namespace App\Filament\Resources\CustomDatasetRowResource\Pages;

use App\Filament\Resources\CustomDatasetRowResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomDatasetRows extends ListRecords
{
    protected static string $resource = CustomDatasetRowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
