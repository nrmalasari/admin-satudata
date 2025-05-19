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
            ->withCount(['datasets as datasets_count' => function ($query) {
                $query->where('is_public', true); // Optional: only count public datasets
            }])
            ->orderBy('name'); // Alphabetical order by default
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

            Tables\Columns\TextColumn::make('datasets_count')
                ->label('Jumlah Dataset')
                ->numeric()
                ->sortable()
                ->alignEnd()
                ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                ->weight(fn (int $state): string => $state > 0 ? 'bold' : 'normal')
                ->formatStateUsing(fn (int $state): string => $state > 0 ? $state : '0')
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
                ->hidden(fn (Organization $record): bool => $record->datasets_count === 0),
        ];
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'datasets_count'; // Default sort by dataset count
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc'; // Default sort direction
    }
}