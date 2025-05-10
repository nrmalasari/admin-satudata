<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\InfografisResource;
use App\Models\Infografis;

class InfografisController extends Controller
{
    public function index()
    {
        $infografis = Infografis::with('sector')
                               ->where('is_published', true)
                               ->orderBy('published_date', 'desc')
                               ->limit(5)
                               ->get();
        return InfografisResource::collection($infografis);
    }
}