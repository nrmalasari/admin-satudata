<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\InfografisResource;
use App\Models\Infografis;
use Illuminate\Support\Facades\Storage;

class InfografisController extends Controller
{
    public function index()
    {
        $infografis = Infografis::with('sector')
                               ->where('is_published', true)
                               ->orderBy('published_date', 'desc')
                               ->get(); // Hapus limit agar semua data diambil
        return InfografisResource::collection($infografis);
    }

    public function show($id)
    {
        $infografis = Infografis::with('sector')
                               ->where('is_published', true)
                               ->find($id);

        if (!$infografis) {
            return response()->json(['message' => 'Infografis tidak ditemukan'], 404);
        }

        // Tambah jumlah view
        $infografis->increment('views');

        return new InfografisResource($infografis);
    }

    public function previewImage($id)
    {
        $infografis = Infografis::where('is_published', true)->findOrFail($id);
        $filePath = storage_path('app/public/' . $infografis->image_path);

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Gambar tidak ditemukan'], 404);
        }

        $mimeType = mime_content_type($filePath);
        $fileName = basename($infografis->image_path);

        // Set header CORS
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, X-Requested-With'
        ];

        return response()->file($filePath, $headers);
    }
}