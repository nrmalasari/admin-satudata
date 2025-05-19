<?php

namespace App\Filament\Resources\CustomDatasetColumnResource\Pages;

use App\Filament\Resources\CustomDatasetColumnResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomDatasetColumns extends ListRecords
{
    protected static string $resource = CustomDatasetColumnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
