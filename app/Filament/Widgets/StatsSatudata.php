<?php

namespace App\Filament\Widgets;

use App\Models\Dataset;
use App\Models\Infografis;
use App\Models\Organization;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsSatudata extends BaseWidget
{

    protected int|string|array $columnSpan = 'full';
    protected function getStats(): array
    {
        return [
            Stat::make('Total Dataset', Dataset::count())
                ->description('Jumlah dataset yang tersedia')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 7])
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
                
            Stat::make('Total Organisasi', Organization::count())
                ->description('Jumlah organisasi terdaftar')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('success')
                ->chart([3, 5, 2, 4, 7, 6, 5, 3])
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
                
            Stat::make('Total Infografis', Infografis::count())
                ->description('Jumlah infografis tersedia')
                ->descriptionIcon('heroicon-o-photo')
                ->color('warning')
                ->chart([5, 3, 7, 4, 2, 6, 5, 8])
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                ]),
        ];
    }
    
    
    public static function getSort(): int
    {
        return 0; // Pastikan ini paling atas
    }
}