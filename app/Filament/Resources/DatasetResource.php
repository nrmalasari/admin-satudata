<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DatasetResource\Pages;
use App\Models\Dataset;
use App\Models\CustomDatasetTable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Log;

class DatasetResource extends Resource
{
    protected static ?string $model = Dataset::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Dataset';
    protected static ?string $navigationLabel = 'Datasets';
    protected static ?string $navigationGroup = 'Data Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dataset')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                            
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function ($state, $set) {
                                Log::info("Selected organization_id: {$state}");
                            }),
                            
                        Forms\Components\Select::make('sector_id')
                            ->relationship('sector', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                            
                        Forms\Components\Select::make('custom_dataset_table_id')
                            ->label('Sumber Tabel Kustom')
                            ->options(fn () => CustomDatasetTable::where('is_public', true)->pluck('title', 'id'))
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $table = CustomDatasetTable::find($state);
                                    if ($table && $table->organization_id) {
                                        $set('organization_id', $table->organization_id);
                                        Log::info("Set organization_id to {$table->organization_id} from custom_dataset_table_id {$state}");
                                    }
                                }
                            })
                            ->hiddenOn('create'),
                            
                        Forms\Components\DatePicker::make('published_date')
                            ->default(now())
                            ->required(),
                            
                        Forms\Components\Select::make('year')
                            ->options(function () {
                                $years = range(now()->year, 2000);
                                return array_combine($years, $years);
                            })
                            ->required()
                            ->default(now()->year),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('File Dataset')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->required()
                            ->directory('datasets')
                            ->preserveFilenames()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'text/csv',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.oasis.opendocument.spreadsheet',
                            ])
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => (string) Str::of($file->getClientOriginalName())
                                    ->prepend(now()->timestamp.'_')
                            )
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $originalName = $state->getClientOriginalName();
                                    $extension = strtolower($state->getClientOriginalExtension());
                                    
                                    $set('file_name', $originalName);
                                    $set('file_type', self::getFileType($extension));
                                    $set('file_size', self::formatBytes($state->getSize()));
                                    
                                    if ($get('custom_dataset_table_id') && empty($get('title'))) {
                                        $table = CustomDatasetTable::find($get('custom_dataset_table_id'));
                                        $set('title', $table?->title ?? $originalName);
                                    }
                                }
                            })
                            ->downloadable()
                            ->openable()
                            ->visibility('public')
                            ->columnSpanFull(),
                            
                        Forms\Components\Hidden::make('file_name'),
                        Forms\Components\Hidden::make('file_type'),
                        Forms\Components\Hidden::make('file_size'),
                    ]),
                    
                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Toggle::make('is_public')
                            ->default(true)
                            ->label('Akses Publik'),
                            
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false)
                            ->label('Ditampilkan'),
                            
                        Forms\Components\TagsInput::make('tags')
                            ->label('Tag')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (\Illuminate\Database\Eloquent\Builder $query) {
                Log::info('Dataset query executed', [
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings(),
                ]);
                return $query->with(['customDatasetTable', 'organization', 'sector']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Dataset $record): string => Str::limit($record->description ?? '', 50)),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organisasi')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('sector.name')
                    ->label('Sektor')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('customDatasetTable.title')
                    ->label('Sumber Tabel')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('year')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('file_type')
                    ->label('Tipe File')
                    ->badge()
                    ->color(fn (string $state): string => self::getFileTypeColor($state)),
                    
                Tables\Columns\TextColumn::make('file_size')
                    ->label('Ukuran'),
                    
                Tables\Columns\TextColumn::make('file_path')
                    ->label('Unduh')
                    ->formatStateUsing(fn () => 'Unduh')
                    ->url(fn (Dataset $record): string => asset('storage/'.$record->file_path))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary'),
                    
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean()
                    ->label('Publik'),
                    
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured'),
                    
                Tables\Columns\TextColumn::make('published_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->options(function () {
                        return Dataset::select('year')
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year', 'year')
                            ->toArray();
                    })
                    ->label('Tahun'),
                    
                Tables\Filters\SelectFilter::make('organization_id')
                    ->relationship('organization', 'name')
                    ->label('Organisasi'),
                    
                Tables\Filters\SelectFilter::make('sector_id')
                    ->relationship('sector', 'name')
                    ->label('Sektor'),
                    
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Status Publik')
                    ->default(null),
                    
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Status Featured')
                    ->default(null),
                    
                Tables\Filters\SelectFilter::make('custom_dataset_table_id')
                    ->options(fn () => CustomDatasetTable::where('is_public', true)->pluck('title', 'id'))
                    ->label('Sumber Tabel Kustom'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Dataset $record) {
                        Storage::disk('public')->delete($record->file_path);
                    }),
                    
                Tables\Actions\Action::make('preview')
                    ->label('Pratinjau')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function (Dataset $record) {
                        return view('filament.preview.dataset', [
                            'record' => $record->load(['customDatasetTable', 'organization', 'sector'])
                        ]);
                    })
                    ->modalWidth('7xl'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            $records->each(function (Dataset $record) {
                                Storage::disk('public')->delete($record->file_path);
                            });
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('published_date', 'desc');
    }

    protected static function getFileType(string $extension): string
    {
        return match(strtolower($extension)) {
            'pdf' => 'pdf',
            'csv' => 'csv',
            'xls', 'xlsx' => 'excel',
            'ods' => 'ods',
            default => $extension
        };
    }

    protected static function formatFileType(string $type): string
    {
        return match($type) {
            'pdf' => 'PDF',
            'csv' => 'CSV',
            'excel' => 'Excel',
            'ods' => 'ODS',
            default => strtoupper($type)
        };
    }

    protected static function getFileTypeColor(string $type): string
    {
        return match($type) {
            'pdf' => 'danger',
            'csv' => 'success',
            'excel' => 'primary',
            'ods' => 'warning',
            default => 'gray'
        };
    }

    protected static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / (1024 ** $pow), $precision).' '.$units[$pow];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDatasets::route('/'),
            'create' => Pages\CreateDataset::route('/create'),
            'edit' => Pages\EditDataset::route('/{record}/edit'),
            'view' => Pages\ViewDataset::route('/{record}'),
        ];
    }
}