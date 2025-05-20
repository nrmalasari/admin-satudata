<?php

namespace App\Filament\Widgets;

use App\Models\Organization;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TotalDatasetPerOPD extends TableWidget
{
    protected static ?string $heading = 'Total Dataset per OPD';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Organization::query()
            ->withCount([
                'datasets as public_datasets_count' => function ($query) {
                    $query->where('is_public', true);
                },
                'customDatasets as public_custom_datasets_count' => function ($query) {
                    $query->where('is_public', true);
                }
            ])
            ->orderByRaw('(public_datasets_count + public_custom_datasets_count) DESC')
            ->orderBy('name');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Nama OPD')
                ->searchable()
                ->sortable()
                ->weight('medium')
                ->description(fn (Organization $record) => $record->description ?? '-')
                ->grow(),

            Tables\Columns\TextColumn::make('public_datasets_count')
                ->label('Dataset Reguler')
                ->numeric()
                ->sortable()
                ->alignEnd()
                ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                ->weight(fn (int $state): string => $state > 0 ? 'bold' : 'normal')
                ->formatStateUsing(fn (int $state): string => $state > 0 ? $state : '0')
                ->width('150px'),
                
            Tables\Columns\TextColumn::make('public_custom_datasets_count')
                ->label('Dataset Kustom')
                ->numeric()
                ->sortable()
                ->alignEnd()
                ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                ->weight(fn (int $state): string => $state > 0 ? 'bold' : 'normal')
                ->formatStateUsing(fn (int $state): string => $state > 0 ? $state : '0')
                ->width('150px'),
                
            Tables\Columns\TextColumn::make('total_datasets')
                ->label('Total Dataset')
                ->numeric()
                ->sortable()
                ->alignEnd()
                ->color('success')
                ->weight('bold')
                ->getStateUsing(fn (Organization $record): int => 
                    $record->public_datasets_count + $record->public_custom_datasets_count
                )
                ->width('150px'),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'Tidak ada organisasi terdaftar';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Silakan tambahkan organisasi terlebih dahulu';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-building-office';
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view_datasets')
                ->label('Lihat Dataset')
                ->url(fn (Organization $record): string => route('filament.admin.resources.datasets.index', [
                    'tableFilters[organization_id][value]' => $record->id
                ]))
                ->icon('heroicon-o-document-text')
                ->hidden(fn (Organization $record): bool => 
                    $record->public_datasets_count === 0 && $record->public_custom_datasets_count === 0
                ),
                
            Tables\Actions\Action::make('view_custom_datasets')
                ->label('Lihat Tabel Kustom')
                ->url(fn (Organization $record): string => route('filament.admin.resources.custom-dataset-tables.index', [
                    'tableFilters[organization_id][value]' => $record->id
                ]))
                ->icon('heroicon-o-table-cells')
                ->hidden(fn (Organization $record): bool => $record->public_custom_datasets_count === 0),
        ];
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return null; // Disable default column sorting as we handle it in the query
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return null; // Disable default direction as we handle it in the query
    }
}