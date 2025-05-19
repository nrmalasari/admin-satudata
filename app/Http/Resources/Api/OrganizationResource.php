<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class OrganizationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_url' => $this->logo_url,
            'description' => $this->description ?? 'Tidak ada deskripsi',
            'dataset_count' => $this->dataset_count ?? 0,
            'last_updated' => $this->when($this->last_updated, function () {
                try {
                    return $this->last_updated->toIso8601String();
                } catch (\Exception $e) {
                    Log::error('Failed to format last_updated for organization ID ' . $this->id . ': ' . $e->getMessage());
                    return null;
                }
            }, null),
            'last_updated_formatted' => $this->last_updated_formatted,
            'sector' => $this->when($this->sector, function () {
                return [
                    'id' => $this->sector->id,
                    'name' => $this->sector->name,
                    'slug' => $this->sector->slug ?? null,
                ];
            }, null),
        ];
    }
}