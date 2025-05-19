<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomTableExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithMapping, WithStyles
{
    protected $data;
    protected $columns;

    public function __construct(array $data, $columns)
    {
        $this->data = $data;
        $this->columns = $columns;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->columns->pluck('header')->toArray();
    }

    public function map($row): array
    {
        $mapped = [];
        foreach ($this->columns as $column) {
            $value = $row[$column->name] ?? null;
            
            switch ($column->type) {
                case 'date':
                    $mapped[] = $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '';
                    break;
                case 'float':
                    $mapped[] = number_format((float)$value, 2);
                    break;
                case 'integer':
                    $mapped[] = number_format((int)$value, 0);
                    break;
                default:
                    $mapped[] = $value;
            }
        }
        return $mapped;
    }

    public function title(): string
    {
        return 'Data Export';
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