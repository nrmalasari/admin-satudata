<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class Dataset extends Model
{
    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'organization_id',
        'sector_id',
        'published_date',
        'year',
        'is_featured',
        'is_public',
        'custom_dataset_table_id',
        'tags',
        'views',
        'downloads',
    ];

    protected $casts = [
        'published_date' => 'date',
        'is_featured' => 'boolean',
        'is_public' => 'boolean',
        'tags' => 'array',
        'views' => 'integer',
        'downloads' => 'integer',
    ];

    protected $attributes = [
        'views' => 0,
        'downloads' => 0,
    ];

    public static function boot()
    {
        parent::boot();

        static::created(function ($dataset) {
            $organization = $dataset->organization;
            if ($organization) {
                Log::info("Dataset created for organization {$organization->id}, updating dataset_count");
                $organization->updateQuietly([
                    'dataset_count' => $organization->datasets()->count() + $organization->customDatasets()->count(),
                    'last_updated' => Carbon::parse($dataset->updated_at),
                ]);
            }
        });

        static::updated(function ($dataset) {
            $originalOrganizationId = $dataset->getOriginal('organization_id');
            $newOrganizationId = $dataset->organization_id;

            if ($originalOrganizationId != $newOrganizationId) {
                if ($originalOrganizationId) {
                    $oldOrganization = Organization::find($originalOrganizationId);
                    if ($oldOrganization) {
                        Log::info("Updating dataset_count for old organization {$oldOrganization->id}");
                        $oldOrganization->updateQuietly([
                            'dataset_count' => $oldOrganization->datasets()->count() + $oldOrganization->customDatasets()->count(),
                            'last_updated' => $oldOrganization->datasets()->latest()->first()?->updated_at,
                        ]);
                    }
                }

                if ($newOrganizationId) {
                    $newOrganization = Organization::find($newOrganizationId);
                    if ($newOrganization) {
                        Log::info("Updating dataset_count for new organization {$newOrganization->id}");
                        $newOrganization->updateQuietly([
                            'dataset_count' => $newOrganization->datasets()->count() + $newOrganization->customDatasets()->count(),
                            'last_updated' => Carbon::parse($dataset->updated_at),
                        ]);
                    }
                }
            } else {
                $organization = $dataset->organization;
                if ($organization) {
                    Log::info("Updating dataset_count for organization {$organization->id}");
                    $organization->updateQuietly([
                        'dataset_count' => $organization->datasets()->count() + $organization->customDatasets()->count(),
                        'last_updated' => Carbon::parse($dataset->updated_at),
                    ]);
                }
            }
        });

        static::deleted(function ($dataset) {
            $organization = $dataset->organization;
            if ($organization) {
                Log::info("Dataset deleted for organization {$organization->id}, updating dataset_count");
                $organization->updateQuietly([
                    'dataset_count' => $organization->datasets()->count() + $organization->customDatasets()->count(),
                    'last_updated' => $organization->datasets()->latest()->first()?->updated_at,
                ]);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function customDatasetTable(): BelongsTo
    {
        return $this->belongsTo(CustomDatasetTable::class);
    }

    public static function createFromCustomTable(CustomDatasetTable $table, string $filePath): self
    {
        if (!$table->exists) {
            throw new \Exception('Tabel kustom tidak valid.');
        }
        if ($table->rows()->count() === 0) {
            throw new \Exception('Tidak ada data baris untuk dipublikasikan.');
        }
        if ($table->columns()->where('visible', true)->count() === 0) {
            throw new \Exception('Tidak ada kolom yang valid untuk dipublikasikan.');
        }

        if (!Storage::disk('public')->exists($filePath)) {
            throw new \Exception('File path tidak ditemukan: ' . $filePath);
        }

        $fileSize = Storage::disk('public')->size($filePath);
        if ($fileSize === false) {
            throw new \Exception('Gagal mendapatkan ukuran file: ' . $filePath);
        }

        $dataset = self::create([
            'title' => $table->title,
            'description' => $table->description ?? 'Dataset dari tabel kustom: ' . $table->title,
            'file_path' => $filePath,
            'file_name' => basename($filePath),
            'file_type' => 'xlsx',
            'file_size' => round($fileSize / 1048576, 2),
            'organization_id' => $table->organization_id,
            'sector_id' => $table->sector_id,
            'published_date' => now(),
            'year' => now()->year,
            'is_featured' => false,
            'is_public' => true,
            'custom_dataset_table_id' => $table->id,
            'tags' => ['kustom', 'generated'],
            'views' => 0,
            'downloads' => 0,
        ]);

        $table->update(['is_public' => true]);

        Log::info('Dataset dibuat dari tabel kustom ID: ' . $table->id . ' dengan dataset ID: ' . $dataset->id);

        return $dataset;
    }
}