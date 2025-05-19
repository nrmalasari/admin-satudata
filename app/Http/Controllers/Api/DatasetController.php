<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DatasetResource;
use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatasetController extends Controller
{
    public function index()
    {
        $datasets = Dataset::with(['organization', 'sector'])
                          ->where('is_public', true)
                          ->orderBy('published_date', 'desc')
                          ->get();
        Log::info('Fetching all datasets');
        return DatasetResource::collection($datasets);
    }

    public function show($id)
    {
        try {
            $dataset = Dataset::with(['organization', 'sector'])
                             ->where('is_public', true)
                             ->findOrFail($id);
            Log::info("Fetching dataset ID: {$id} without incrementing view");
            return new DatasetResource($dataset);
        } catch (\Exception $e) {
            Log::error("Failed to fetch dataset ID: {$id}", [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Dataset tidak ditemukan'], 404);
        }
    }

    public function incrementView($id)
    {
        try {
            $dataset = Dataset::where('is_public', true)->findOrFail($id);
            $sessionKey = "view_dataset_{$id}";
            $lastIncrementKey = "last_increment_dataset_{$id}";
            $currentTime = now()->timestamp;
            $lastIncrementTime = session()->get($lastIncrementKey, 0);
            $timeThreshold = 60; // Izinkan increment setiap 60 detik

            Log::info("Checking view increment for dataset ID: {$id}", [
                'session_key' => $sessionKey,
                'last_increment_time' => $lastIncrementTime,
                'current_time' => $currentTime,
                'time_diff' => $currentTime - $lastIncrementTime,
            ]);

            if (($currentTime - $lastIncrementTime) < $timeThreshold) {
                Log::info("View increment throttled for dataset ID: {$id}", [
                    'time_diff' => $currentTime - $lastIncrementTime,
                    'threshold' => $timeThreshold,
                ]);
                return response()->json([
                    'data' => ['status' => 'already_incremented']
                ], 200, [
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, X-Request-ID, X-Visit-ID'
                ]);
            }

            $dataset->increment('views');
            session()->put($sessionKey, true);
            session()->put($lastIncrementKey, $currentTime);
            Log::info("Incremented view for dataset ID: {$id}", [
                'new_view_count' => $dataset->views,
                'session_data' => session()->all(),
            ]);

            return response()->json([
                'data' => ['status' => 'success']
            ], 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, X-Request-ID, X-Visit-ID'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to increment view for dataset ID: {$id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to increment view'], 500);
        }
    }

    public function incrementDownload($id)
    {
        try {
            $dataset = Dataset::where('is_public', true)->findOrFail($id);
            $dataset->increment('downloads');
            Log::info("Incremented download count for dataset ID: {$id}", [
                'new_download_count' => $dataset->downloads
            ]);
            return response()->json([
                'data' => ['status' => 'success']
            ], 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, X-Request-ID, X-Visit-ID'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to increment download for dataset ID: {$id}", [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to increment download'], 500);
        }
    }

    public function previewFile($id)
    {
        try {
            $dataset = Dataset::where('is_public', true)->findOrFail($id);
            $filePath = storage_path('app/public/' . $dataset->file_path);

            if (!file_exists($filePath)) {
                Log::warning("File not found for dataset ID: {$id}", ['path' => $filePath]);
                return response()->json(['message' => 'File tidak ditemukan'], 404);
            }

            $mimeType = mime_content_type($filePath);
            $fileName = $dataset->file_name;

            $headers = [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, X-Request-ID, X-Visit-ID'
            ];

            if (str_contains($mimeType, 'pdf') || str_contains($mimeType, 'image')) {
                Log::info("Serving preview file for dataset ID: {$id}");
                return response()->file($filePath, $headers);
            } else {
                $headers['Content-Disposition'] = 'attachment; filename="' . $fileName . '"';
                Log::info("Serving download file for dataset ID: {$id}");
                return response()->file($filePath, $headers);
            }
        } catch (\Exception $e) {
            Log::error("Failed to preview file for dataset ID: {$id}", [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Gagal memuat file'], 500);
        }
    }

    public function downloadFile($id)
    {
        try {
            $dataset = Dataset::where('is_public', true)->findOrFail($id);
            $filePath = storage_path('app/public/' . $dataset->file_path);
            $fileName = $dataset->file_name;

            if (!file_exists($filePath)) {
                Log::warning("File not found for dataset ID: {$id}", ['path' => $filePath]);
                return response()->json(['message' => 'File tidak ditemukan'], 404);
            }

            $mimeType = mime_content_type($filePath);

            $headers = [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With, X-Request-ID, X-Visit-ID'
            ];

            Log::info("Serving download file for dataset ID: {$id}");
            return response()->file($filePath, $headers);
        } catch (\Exception $e) {
            Log::error("Failed to download file for dataset ID: {$id}", [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Gagal mengunduh file'], 500);
        }
    }
}