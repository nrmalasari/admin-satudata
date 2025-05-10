<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'description',
        'sector_id',
        'dataset_count',
        'last_updated'
    ];

    protected $appends = ['logo_url'];
    protected $withCount = ['datasets'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            $organization->slug = Str::slug($organization->name);
        });

        static::updating(function ($organization) {
            $organization->slug = Str::slug($organization->name);
        });

        static::saved(function ($organization) {
            $organization->updateQuietly([
                'dataset_count' => $organization->datasets()->count(),
                'last_updated' => $organization->datasets()->latest()->first()?->updated_at
            ]);
        });
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function datasets()
    {
        return $this->hasMany(Dataset::class);
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function getNameWithDatasetsAttribute()
    {
        return "{$this->name} ({$this->datasets_count} dataset)";
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
    }
}