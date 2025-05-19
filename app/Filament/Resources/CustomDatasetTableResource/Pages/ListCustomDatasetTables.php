<?php

namespace App\Filament\Resources\CustomDatasetTableResource\Pages;

use App\Filament\Resources\CustomDatasetTableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomDatasetTables extends ListRecords
{
    protected static string $resource = CustomDatasetTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
