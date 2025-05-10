<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sector extends Model
{
    protected $fillable = ['name', 'slug', 'icon'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sector) {
            $sector->slug = Str::slug($sector->name);
        });

        static::updating(function ($sector) {
            $sector->slug = Str::slug($sector->name);
        });
    }

    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            return asset('storage/' . $this->icon); // Contoh: http://localhost:8000/storage/sector-icons/icon_abc123.png
        }
        return '/images/default-sector.png'; // Gambar cadangan
    }

    public function organizations()
    {
        return $this->hasMany(\App\Models\Organization::class);
    }
}