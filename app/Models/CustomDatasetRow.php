<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CustomDatasetRow extends Model
{
    protected $fillable = [
        'custom_dataset_table_id',
        'data'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function customDatasetTable()
    {
        return $this->belongsTo(CustomDatasetTable::class);
    }

    public function table()
    {
        return $this->belongsTo(CustomDatasetTable::class, 'custom_dataset_table_id');
    }
}