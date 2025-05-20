<?php

namespace App\Filament\Resources;

use App\Exports\CustomTableExport;
use App\Filament\Resources\CustomDatasetTableResource\Pages;
use App\Models\CustomDatasetTable;
use App\Models\Dataset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CustomDatasetTableResource extends Resource
{
    protected static ?string $model = CustomDatasetTable::class;
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationLabel = 'Tabel Kustom';
    protected static ?string $pluralLabel = 'Tabel Kustom';
    protected $with = ['organization', 'sector'];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tabel')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Nama Tabel')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('organization_id')
                            ->label('Organisasi')
                            ->relationship('organization', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(50),
                            
                        Forms\Components\Select::make('sector_id')
                            ->label('Sektor')
                            ->relationship('sector', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->optionsLimit(50),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Select::make('table_type')
                            ->label('Tipe Tabel')
                            ->options([
                                'manual' => 'Manual',
                                'excel' => 'Excel',
                                'api' => 'API'
                            ])
                            ->default('manual')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('editable', $state === 'manual');
                            }),
                            
                        Forms\Components\Toggle::make('editable')
                            ->label('Dapat Diedit')
                            ->default(true)
                            ->disabled(fn (callable $get) => $get('table_type') !== 'manual'),
                            
                        Forms\Components\Toggle::make('is_public')
                            ->label('Publikasikan sebagai Dataset')
                            ->default(false)
                            ->hidden(fn (string $operation): bool => $operation === 'create'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Nama Tabel')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => Str::limit($record->description, 30)),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organisasi')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('sector.name')
                    ->label('Sektor')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('columns_count')
                    ->label('Kolom')
                    ->counts('columns'),
                    
                Tables\Columns\TextColumn::make('rows_count')
                    ->label('Baris')
                    ->counts('rows'),
                    
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Publik')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('organization')
                    ->relationship('organization', 'name')
                    ->label('Organisasi'),
                    
                Tables\Filters\SelectFilter::make('sector')
                    ->relationship('sector', 'name')
                    ->label('Sektor'),
                    
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Status Publikasi'),
            ])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('Pratinjau')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function ($record) {
                        Log::info('Menampilkan pratinjau untuk tabel ID: ' . $record->id);
                        $record->load('organization', 'sector');
                        $columns = $record->columns()->orderBy('order_index')->get();
                        $rows = $record->rows()->limit(10)->get();
                        
                        return view('filament.preview.custom-table', [
                            'record' => $record,
                            'columns' => $columns,
                            'rows' => $rows
                        ]);
                    })
                    ->modalWidth('7xl'),
                    
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('manage_columns')
                        ->label('Kelola Kolom')
                        ->icon('heroicon-o-view-columns')
                        ->url(fn ($record) => CustomDatasetColumnResource::getUrl('index', ['table_id' => $record->id])),
                    
                    Tables\Actions\Action::make('manage_rows')
                        ->label('Kelola Data')
                        ->icon('heroicon-o-document-text')
                        ->url(fn ($record) => CustomDatasetRowResource::getUrl('index', ['table_id' => $record->id])),
                    
                    Tables\Actions\Action::make('export_excel')
                        ->label('Ekspor Excel')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn ($record) => static::exportToExcel($record))
                        ->color('success'),
                    
                    Tables\Actions\Action::make('publish_dataset')
                        ->label('Publikasikan')
                        ->icon('heroicon-o-globe-alt')
                        ->action(fn ($record) => static::publishToDataset($record))
                        ->color('primary')
                        ->hidden(fn ($record) => $record->is_public),
                        
                    Tables\Actions\Action::make('unpublish_dataset')
                        ->label('Batalkan Publikasi')
                        ->icon('heroicon-o-lock-closed')
                        ->action(fn ($record) => static::unpublishDataset($record))
                        ->color('danger')
                        ->visible(fn ($record) => $record->is_public),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function exportToExcel($record)
    {
        try {
            Log::info('Memulai ekspor Excel untuk tabel: ' . $record->title, [
                'table_id' => $record->id
            ]);

            // Ambil kolom yang visible
            $columns = $record->columns()
                ->where('visible', true)
                ->orderBy('order_index')
                ->get();

            Log::info('Kolom yang diambil:', ['columns' => $columns->toArray()]);

            if ($columns->isEmpty()) {
                throw new \Exception('Tidak ada kolom yang tersedia untuk diekspor.');
            }

            // Ambil baris
            $rows = $record->rows()->get();
            Log::info('Baris yang diambil:', [
                'jumlah_baris' => $rows->count(),
                'baris' => $rows->toArray()
            ]);

            if ($rows->isEmpty()) {
                throw new \Exception('Tidak ada baris data untuk diekspor.');
            }

            // Proses data baris
            $data = $rows->map(function ($row) use ($columns) {
                $rowJson = is_array($row->data) ? $row->data : json_decode($row->data, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('JSON tidak valid di data baris:', [
                        'row_id' => $row->id,
                        'data' => $row->data,
                        'error' => json_last_error_msg()
                    ]);
                    return null;
                }

                $rowData = [];
                foreach ($columns as $column) {
                    $rowData[$column->name] = $rowJson[$column->name] ?? null;
                }

                return $rowData;
            })->filter()->values()->toArray();

            Log::info('Data yang dipersiapkan untuk ekspor:', ['data' => $data]);

            if (empty($data)) {
                throw new \Exception('Tidak ada data valid untuk diekspor setelah pemrosesan.');
            }

            // Buat file ekspor
            Storage::disk('public')->makeDirectory('datasets/exports');
            $fileName = Str::slug($record->title) . '_' . now()->format('YmdHis') . '.xlsx';
            $filePath = 'datasets/exports/' . $fileName;
            $fullPath = Storage::disk('public')->path($filePath);

            Excel::store(
                new CustomTableExport($data, $columns),
                $filePath,
                'public'
            );

            if (!file_exists($fullPath)) {
                throw new \Exception('Gagal membuat file Excel.');
            }

            return response()->download($fullPath)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            Log::error('Error saat ekspor Excel:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'table_id' => $record->id,
            ]);

            return redirect()->back()
                ->with('error', 'Gagal mengekspor: ' . $e->getMessage());
        }
    }

    public static function publishToDataset($record)
    {
        try {
            DB::beginTransaction();

            Log::info('Memulai publikasi dataset untuk tabel: ' . $record->title, [
                'table_id' => $record->id
            ]);

            if (!$record->exists) {
                throw new \Exception('Tabel tidak valid.');
            }

            // Ambil kolom
            $columns = $record->columns()->where('visible', true)->orderBy('order_index')->get();
            if ($columns->isEmpty()) {
                throw new \Exception('Tidak ada kolom yang valid untuk dipublikasikan.');
            }

            // Ambil baris
            $rows = $record->rows()->get();
            if ($rows->isEmpty()) {
                throw new \Exception('Tidak ada data untuk dipublikasikan.');
            }

            // Proses data baris
            $data = $rows->map(function ($row) use ($columns) {
                $rowJson = is_array($row->data) ? $row->data : json_decode($row->data, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('JSON tidak valid di data baris:', [
                        'row_id' => $row->id,
                        'data' => $row->data,
                        'error' => json_last_error_msg()
                    ]);
                    return null;
                }

                $rowData = [];
                foreach ($columns as $column) {
                    $rowData[$column->name] = $rowJson[$column->name] ?? null;
                }

                return $rowData;
            })->filter()->values()->toArray();

            Log::info('Data yang dipersiapkan untuk publikasi:', ['data' => $data]);

            if (empty($data)) {
                throw new \Exception('Tidak ada data valid untuk dipublikasikan setelah pemrosesan.');
            }

            // Buat file dataset
            Storage::disk('public')->makeDirectory('datasets');
            $fileName = Str::slug($record->title) . '_dataset_' . now()->format('YmdHis') . '.xlsx';
            $filePath = 'datasets/' . $fileName;

            Excel::store(
                new CustomTableExport($data, $columns),
                $filePath,
                'public'
            );

            $fullPath = Storage::disk('public')->path($filePath);
            if (!file_exists($fullPath)) {
                throw new \Exception('Gagal membuat file Excel untuk publikasi.');
            }

            // Buat dataset
            $dataset = Dataset::createFromCustomTable($record, $filePath);

            DB::commit();

            return redirect()->route('filament.admin.resources.datasets.view', ['record' => $dataset->id])
                ->with('success', 'Tabel berhasil dipublikasikan sebagai Dataset!');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            Log::error('Error saat publikasi dataset:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'table_id' => $record->id,
            ]);

            return redirect()->back()
                ->with('error', 'Gagal mempublikasikan: ' . $e->getMessage());
        }
    }

    public static function unpublishDataset($record)
    {
        try {
            DB::beginTransaction();
            
            $dataset = Dataset::where('custom_dataset_table_id', $record->id)->first();
            
            if ($dataset) {
                Storage::disk('public')->delete($dataset->file_path);
                $dataset->delete();
            }
            
            $record->update(['is_public' => false]);
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', 'Publikasi dataset berhasil dibatalkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error saat membatalkan publikasi dataset:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'table_id' => $record->id,
            ]);
            
            return redirect()->back()
                ->with('error', 'Gagal membatalkan publikasi: ' . $e->getMessage());
        }
    }

    public function scopeFromOrganization($query, $orgId)
    {
        return $query->where('organization_id', $orgId);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomDatasetTables::route('/'),
            'create' => Pages\CreateCustomDatasetTable::route('/create'),
            'edit' => Pages\EditCustomDatasetTable::route('/{record}/edit'),
        ];
    }
}