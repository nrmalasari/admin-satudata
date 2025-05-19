<?php

namespace App\Filament\Widgets;

use App\Models\Dataset;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DatasetPertahunChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Dataset per Tahun';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';
    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $datasetCounts = Dataset::query()
            ->select('year', DB::raw('COUNT(*) as count'))
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        $minYear = $datasetCounts->min('year') ?: now()->subYears(5)->year;
        $maxYear = $datasetCounts->max('year') ?: now()->year;

        $allYears = collect(range($minYear, $maxYear));

        $data = $allYears->mapWithKeys(function ($year) use ($datasetCounts) {
            $count = $datasetCounts->firstWhere('year', $year);
            return [$year => $count ? $count->count : 0];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Dataset',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#2563eb',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data->keys()->map(fn ($year) => (string)$year)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => '#1e293b',
                    'titleFont' => ['size' => 12],
                    'bodyFont' => ['size' => 12],
                    'cornerRadius' => 4,
                    'padding' => 8,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['color' => 'rgba(226, 232, 240, 0.5)'],
                    'ticks' => [
                        'color' => '#64748b',
                        'font' => ['size' => 11],
                        'precision' => 0,
                    ],
                ],
                'x' => [
                    'grid' => ['display' => false],
                    'ticks' => [
                        'color' => '#64748b',
                        'font' => ['size' => 11],
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
            'responsive' => true,
        ];
    }
}