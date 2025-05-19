<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

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
        'last_updated',
    ];

    protected $casts = [
        'last_updated' => 'datetime',
    ];

    protected $appends = ['logo_url'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($organization) {
            $organization->slug = Str::slug($organization->name);
        });

        static::updating(function ($organization) {
            $organization->slug = Str::slug($organization->name);
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

    public function customDatasets()
    {
        return $this->hasMany(CustomDatasetTable::class);
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : asset('images/default-organization.png');
    }

    public function getLastUpdatedFormattedAttribute()
    {
        return $this->last_updated ? $this->last_updated->locale('id')->translatedFormat('d F Y') : 'Belum pernah diperbarui';
    }

    public function getLastUpdatedAttribute($value)
    {
        try {
            return $value ? Carbon::parse($value) : null;
        } catch (\Exception $e) {
            Log::error('Invalid last_updated format for organization ID ' . $this->id . ': ' . $value);
            return null;
        }
    }

    public function getDatasetCountAttribute()
    {
        try {
            return $this->datasets()->count() + $this->customDatasets()->count();
        } catch (\Exception $e) {
            Log::error('Error calculating dataset_count for organization ID ' . $this->id . ': ' . $e->getMessage());
            return 0;
        }
    }
}