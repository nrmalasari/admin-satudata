<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomDatasetColumn extends Model
{
    protected $fillable = [
        'custom_dataset_table_id',
        'name',
        'header',
        'type',
        'visible',
        'order_index',
        'filter_type'
    ];

    protected $casts = [
        'visible' => 'boolean',
    ];

    // Validasi sebelum menyimpan
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->custom_dataset_table_id)) {
                throw new \Exception('Kolom harus terkait dengan tabel kustom');
            }
        });
    }

    // Relasi ke tabel induk
    public function customDatasetTable(): BelongsTo
    {
        return $this->belongsTo(CustomDatasetTable::class);
    }
}