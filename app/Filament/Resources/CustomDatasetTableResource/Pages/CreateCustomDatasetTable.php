<?php

namespace App\Filament\Resources\CustomDatasetTableResource\Pages;

use App\Filament\Resources\CustomDatasetTableResource;
use App\Models\CustomDatasetTable;
use App\Models\Organization;
use App\Models\Sector;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateCustomDatasetTable extends CreateRecord
{
    protected static string $resource = CustomDatasetTableResource::class;

    protected function handleRecordCreation(array $data): CustomDatasetTable
    {
        try {
            Log::info('Mencoba membuat tabel kustom dengan data: ', $data);

            $requiredFields = ['title', 'organization_id', 'sector_id', 'table_type'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new \Exception("Field wajib '$field' tidak ada atau kosong");
                }
            }

            $organization = Organization::find($data['organization_id']);
            if (!$organization) {
                throw new \Exception("Organisasi dengan ID {$data['organization_id']} tidak ditemukan di database");
            }

            $sector = Sector::find($data['sector_id']);
            if (!$sector) {
                throw new \Exception("Sektor dengan ID {$data['sector_id']} tidak ditemukan di database");
            }

            $data['is_public'] = false;
            $record = static::getModel()::create($data);

            Log::info('Tabel kustom berhasil dibuat dengan ID: ' . $record->id);

            return $record;
        } catch (\Exception $e) {
            Log::error('Error saat membuat tabel kustom:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}