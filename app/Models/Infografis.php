<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Infografis extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'image_path',
        'views',
        'published_date',
        'sector_id',
        'is_featured',
        'is_published'
    ];

    protected $casts = [
        'published_date' => 'date',
        'is_featured' => 'boolean',
        'is_published' => 'boolean'
    ];

    protected static function booted()
    {
        static::creating(function ($infografis) {
            if (empty($infografis->slug)) {
                $infografis->slug = Str::slug($infografis->title);
            }
        });

        static::updating(function ($infografis) {
            if (empty($infografis->slug)) {
                $infografis->slug = Str::slug($infografis->title);
            }
        });
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path); // Contoh: http://localhost:8000/storage/infografis/infografis_abc123.jpg
        }
        return '/images/default-infografis.jpg'; // Gambar cadangan
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
}