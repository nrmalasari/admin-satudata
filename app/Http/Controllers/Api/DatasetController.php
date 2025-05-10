<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DatasetResource;
use App\Models\Dataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatasetController extends Controller
{
    public function index()
    {
        $datasets = Dataset::with(['organization', 'sector'])
                          ->where('is_public', true)
                          ->orderBy('published_date', 'desc')
                          ->get();
        return DatasetResource::collection($datasets);
    }

    public function show($id)
    {
        $dataset = Dataset::with(['organization', 'sector'])
                         ->where('is_public', true)
                         ->find($id);

        if (!$dataset) {
            return response()->json(['message' => 'Dataset tidak ditemukan'], 404);
        }

        // Increment view count
        $dataset->increment('views');

        return new DatasetResource($dataset);
    }

    public function previewFile($id)
    {
        $dataset = Dataset::where('is_public', true)->findOrFail($id);
        $filePath = storage_path('app/public/' . $dataset->file_path);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }

        $mimeType = mime_content_type($filePath);
        $fileName = $dataset->file_name;

        // Set CORS headers
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
        ];

        // Handle different file types
        if (str_contains($mimeType, 'pdf') || str_contains($mimeType, 'image')) {
            return response()->file($filePath, $headers);
        } else {
            // For other file types, offer download
            $headers['Content-Disposition'] = 'attachment; filename="' . $fileName . '"';
            return response()->file($filePath, $headers);
        }
    }

    public function downloadFile($id)
    {
        $dataset = Dataset::where('is_public', true)->findOrFail($id);
        $filePath = storage_path('app/public/' . $dataset->file_path);
        $fileName = $dataset->file_name;

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }

        // Increment download count
        $dataset->increment('downloads');

        $mimeType = mime_content_type($filePath);

        // Set CORS headers
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
        ];

        return response()->file($filePath, $headers);
    }
}