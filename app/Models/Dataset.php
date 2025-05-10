<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Dataset extends Model
{
    protected $fillable = [
        'title', 'description', 'file_path', 'file_name', 'file_type', 'file_size',
        'views', 'downloads', 'published_date', 'year', 'organization_id', 'sector_id',
        'tags', 'is_featured', 'is_public'
    ];
    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'published_date' => 'date'
    ];
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
}