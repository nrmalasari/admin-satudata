<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1', 'as' => 'api.'], function() {
    Route::get('/sectors', [\App\Http\Controllers\Api\SectorController::class, 'index']);
    Route::get('/organizations', [\App\Http\Controllers\Api\OrganizationController::class, 'index']);

    Route::get('/datasets', [\App\Http\Controllers\Api\DatasetController::class, 'index']);
    Route::get('/datasets/{id}', [\App\Http\Controllers\Api\DatasetController::class, 'show']);
    Route::get('/datasets/{id}/preview', [\App\Http\Controllers\Api\DatasetController::class, 'previewFile']);
    Route::get('/datasets/{id}/download', [\App\Http\Controllers\Api\DatasetController::class, 'downloadFile']);

    Route::get('/infografis', [\App\Http\Controllers\Api\InfografisController::class, 'index']);

    Route::get('/stats', function() {
        return response()->json([
            'total_datasets' => \App\Models\Dataset::where('is_public', true)->count(),
            'total_organizations' => \App\Models\Organization::count(),
            'total_infografis' => \App\Models\Infografis::where('is_published', true)->count(),
            'total_sectors' => \App\Models\Sector::count()
        ]);
    });
});
