<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Log;

class CustomTableExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithMapping, WithStyles
{
    protected $data;
    protected $columns;

    public function __construct(array $data, $columns)
    {
        Log::info('Inisialisasi CustomTableExport:', [
            'jumlah_data' => count($data),
            'kolom' => $columns->toArray()
        ]);
        $this->data = $data;
        $this->columns = $columns;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        $headings = $this->columns->pluck('header')->toArray();
        Log::info('Header ekspor:', ['header' => $headings]);
        return $headings;
    }

    public function map($row): array
    {
        Log::info('Memetakan baris:', ['baris' => $row]);
        $mapped = [];
        foreach ($this->columns as $column) {
            $value = $row[$column->name] ?? null;
            Log::info('Memetakan kolom:', [
                'kolom' => $column->name,
                'tipe' => $column->type,
                'nilai' => $value
            ]);

            switch ($column->type) {
                case 'date':
                    $mapped[] = $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '';
                    break;
                case 'float':
                    $mapped[] = $value !== null ? number_format((float)$value, 2, ',', '.') : '';
                    break;
                case 'integer':
                    $mapped[] = $value !== null ? number_format((int)$value, 0, ',', '.') : '';
                    break;
                default:
                    $mapped[] = $value ?? '';
            }
        }
        Log::info('Baris yang dipetakan:', ['mapped' => $mapped]);
        return $mapped;
    }

    public function title(): string
    {
        return 'Data Ekspor';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:Z' => [
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                ],
            ],
        ];
    }
}