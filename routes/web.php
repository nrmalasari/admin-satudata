<?php

use Illuminate\Support\Facades\Route;

use App\Filament\Resources\CustomDatasetTableResource;

Route::get('/custom-tables/export/{id}', function ($id) {
    $table = CustomDatasetTableResource::getModel()::findOrFail($id);
    return CustomDatasetTableResource::exportToExcel($table);
})->name('custom-tables.export');
