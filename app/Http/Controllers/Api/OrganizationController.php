<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrganizationResource;
use App\Models\Organization;

class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::with(['sector'])->withCount('datasets')->get();
        return OrganizationResource::collection($organizations);
    }
}

