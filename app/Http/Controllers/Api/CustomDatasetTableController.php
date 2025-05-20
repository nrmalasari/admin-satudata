<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomDatasetTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomDatasetTableController extends Controller
{
    // Mengambil daftar tabel untuk organisasi tertentu
    public function getTablesByOrganization($organizationId)
    {
        try {
            $tables = CustomDatasetTable::where('organization_id', $organizationId)
                ->where('is_public', true)
                ->get(['id', 'title', 'description']);

            Log::info("Mengambil daftar tabel untuk organisasi ID: {$organizationId}");
            return response()->json([
                'success' => true,
                'data' => $tables,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Gagal mengambil tabel untuk organisasi ID: {$organizationId}", [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar tabel',
            ], 500);
        }
    }

    // Mengambil data tabel berdasarkan organization_id dan table_id
    public function show($organizationId, $tableId)
    {
        try {
            $table = CustomDatasetTable::where('id', $tableId)
                ->where('organization_id', $organizationId)
                ->where('is_public', true)
                ->with([
                    'columns' => function ($query) {
                        $query->where('visible', true)->orderBy('order_index');
                    },
                    'rows'
                ])
                ->firstOrFail();

            $response = [
                'id' => $table->id,
                'title' => $table->title,
                'description' => $table->description,
                'columns' => $table->columns->map(function ($column) {
                    return [
                        'name' => $column->name,
                        'header' => $column->header,
                        'type' => $column->type,
                        'order_index' => $column->order_index,
                    ];
                })->toArray(),
                'rows' => $table->rows->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'data' => $row->data,
                    ];
                })->toArray(),
            ];

            Log::info("Mengambil data tabel kustom ID: {$tableId} untuk organisasi ID: {$organizationId}");
            return response()->json([
                'success' => true,
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Gagal mengambil tabel kustom ID: {$tableId} untuk organisasi ID: {$organizationId}", [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Tabel tidak ditemukan, tidak publik, atau tidak terkait dengan organisasi',
            ], 404);
        }
    }
}