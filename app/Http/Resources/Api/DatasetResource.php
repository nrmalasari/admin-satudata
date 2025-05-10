<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class DatasetResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'file_path' => $this->file_path, // Full URL, e.g., http://localhost:8000/storage/datasets/filename.pdf
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'views' => $this->views,
            'downloads' => $this->downloads,
            'published_date' => $this->published_date,
            'year' => $this->year,
            'organization_id' => $this->organization_id,
            'sector_id' => $this->sector_id,
            'organization' => $this->whenLoaded('organization', fn () => [
                'id' => $this->organization->id,
                'name' => $this->organization->name
            ]),
            'sector' => $this->whenLoaded('sector', fn () => [
                'id' => $this->sector->id,
                'name' => $this->sector->name
            ]),
            'tags' => $this->tags ?? [],
            'is_public' => $this->is_public,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}