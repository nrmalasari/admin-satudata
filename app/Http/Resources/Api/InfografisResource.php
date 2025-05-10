<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class InfografisResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'image_url' => $this->image_url, // Dari aksesor getImageUrlAttribute
            'views' => $this->views,
            'published_date' => $this->published_date ? $this->published_date->toDateString() : null,
            'sector' => [
                'id' => $this->sector->id,
                'name' => $this->sector->name,
                'slug' => $this->sector->slug,
            ],
            'is_featured' => $this->is_featured,
            'is_published' => $this->is_published,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}