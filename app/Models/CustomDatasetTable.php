<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomDatasetTable extends Model
{
    protected $fillable = [
        'title',  
        'organization_id', 
        'sector_id', 
        'table_type', 
        'editable', 
        'is_public',
        'description',
    ];

    public function columns()
    {
        return $this->hasMany(CustomDatasetColumn::class);
    }

    public function rows()
    {
        return $this->hasMany(CustomDatasetRow::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }

    public function dataset()
    {
        return $this->hasOne(Dataset::class, 'custom_dataset_table_id');
    }
}