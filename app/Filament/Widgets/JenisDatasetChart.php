<?php

namespace App\Filament\Widgets;

use App\Models\Dataset;
use Filament\Widgets\ChartWidget;

class JenisDatasetChart extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Jenis Dataset';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '150px';
    protected int|string|array $columnSpan = 1;
    protected static ?string $pollingInterval = '10s';

    protected function getData(): array
    {
        $datasets = Dataset::select('file_type')
            ->selectRaw('count(*) as count')
            ->groupBy('file_type')
            ->get();

        return [
            'labels' => $datasets->map(fn ($d) => $this->formatTypeLabel($d->file_type)),
            'datasets' => [
                [
                    'data' => $datasets->pluck('count'),
                    'backgroundColor' => $datasets->map(fn ($d) => $this->getTypeBackgroundColor($d->file_type)),
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                    'hoverOffset' => 4,
                    'hoverBorderColor' => '#94a3b8',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => [
                        'boxWidth' => 14,
                        'padding' => 12,
                        'font' => [
                            'size' => 11,
                            'family' => "'Inter', sans-serif",
                        ],
                        'color' => '#64748b',
                        'usePointStyle' => true,
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => '#1e293b',
                    'titleFont' => ['size' => 12],
                    'bodyFont' => ['size' => 12],
                    'cornerRadius' => 4,
                    'padding' => 8,
                    'displayColors' => false,
                ],
            ],
            'cutout' => '55%',
            'radius' => '95%',
            'maintainAspectRatio' => false,
            'animation' => [
                'animateScale' => true,
                'animateRotate' => true,
            ],
        ];
    }

    private function formatTypeLabel(string $type): string
    {
        return match($type) {
            'pdf' => 'PDF Documents',
            'csv' => 'CSV Files',
            'excel' => 'Excel Spreadsheets',
            'ods' => 'OpenDocument Sheets',
            default => strtoupper($type)
        };
    }

    private function getTypeBackgroundColor(string $type): string
    {
        return match($type) {
            'pdf' => 'rgba(239, 68, 68, 0.8)',
            'csv' => 'rgba(34, 197, 94, 0.8)',
            'excel' => 'rgba(59, 130, 246, 0.8)',
            'ods' => 'rgba(245, 158, 11, 0.8)',
            default => 'rgba(107, 114, 128, 0.8)'
        };
    }
}