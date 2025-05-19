<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $search = $request->input('search');

            $query = Organization::with(['sector'])
                ->when($search, function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhereHas('sector', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                })
                ->select(['id', 'name', 'slug', 'logo_path', 'description', 'sector_id', 'dataset_count', 'last_updated'])
                ->orderBy('name');

            $organizations = $query->paginate($perPage);

            return OrganizationResource::collection($organizations);
        } catch (\Exception $e) {
            Log::error('Error fetching organizations', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_id' => $request->header('X-Request-ID'),
                'search' => $request->input('search'),
            ]);

            return response()->json([
                'error' => 'Failed to fetch organizations',
                'message' => env('APP_DEBUG') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $organization = Organization::with(['sector'])
                ->select(['id', 'name', 'slug', 'logo_path', 'description', 'sector_id', 'dataset_count', 'last_updated'])
                ->findOrFail($id);

            return new OrganizationResource($organization);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Organization not found', [
                'id' => $id,
                'request_id' => $request->header('X-Request-ID'),
            ]);

            return response()->json([
                'error' => 'Organization not found',
                'message' => "No organization found with ID {$id}",
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching organization', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_id' => $request->header('X-Request-ID'),
            ]);

            return response()->json([
                'error' => 'Failed to fetch organization',
                'message' => env('APP_DEBUG') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}