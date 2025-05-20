<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'as' => 'api.'], function() {
    Route::get('/sectors', [\App\Http\Controllers\Api\SectorController::class, 'index']);
    Route::get('/organizations', [\App\Http\Controllers\Api\OrganizationController::class, 'index']);
    Route::get('/organizations/{id}', [\App\Http\Controllers\Api\OrganizationController::class, 'show']);
    Route::get('/organizations/{organizationId}/tables/{tableId}', [\App\Http\Controllers\Api\CustomDatasetTableController::class, 'show']);
    Route::get('/tables/{id}', [\App\Http\Controllers\Api\CustomDatasetTableController::class, 'show']);
    

    Route::get('/datasets', [\App\Http\Controllers\Api\DatasetController::class, 'index']);
    Route::get('/datasets/{id}', [\App\Http\Controllers\Api\DatasetController::class, 'show']);
    Route::get('/datasets/{id}/preview', [\App\Http\Controllers\Api\DatasetController::class, 'previewFile']);
    Route::get('/datasets/{id}/download', [\App\Http\Controllers\Api\DatasetController::class, 'downloadFile']);
    Route::post('/datasets/{id}/increment-view', [\App\Http\Controllers\Api\DatasetController::class, 'incrementView']);

    Route::get('/infografis', [\App\Http\Controllers\Api\InfografisController::class, 'index']);
    Route::get('/infografis/{id}', [\App\Http\Controllers\Api\InfografisController::class, 'show']);
    Route::get('/infografis/{id}/preview', [\App\Http\Controllers\Api\InfografisController::class, 'previewImage']);

    Route::get('/stats', function() {
        return response()->json([
            'total_datasets' => \App\Models\Dataset::where('is_public', true)->count(),
            'total_organizations' => \App\Models\Organization::count(),
            'total_infografis' => \App\Models\Infografis::where('is_published', true)->count(),
            'total_sectors' => \App\Models\Sector::count()
        ]);
    });
});
