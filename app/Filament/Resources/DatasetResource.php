<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DatasetResource\Pages;
use App\Models\Dataset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;

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
                Forms\Components\Section::make('Dataset Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                            
                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->required(),
                            
                        Forms\Components\Select::make('sector_id')
                            ->relationship('sector', 'name')
                            ->required(),
                            
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
                    ])->columns(2),
                    
                Forms\Components\Section::make('File Upload')
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
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $originalName = $state->getClientOriginalName();
                                    $extension = strtolower($state->getClientOriginalExtension());
                                    
                                    $set('file_name', $originalName);
                                    $set('file_type', self::getFileType($extension));
                                    $set('file_size', self::formatBytes($state->getSize()));
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
                    
                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_public')
                            ->default(true)
                            ->label('Make this dataset publicly accessible'),
                            
                        Forms\Components\Toggle::make('is_featured')
                            ->default(false)
                            ->label('Feature this dataset'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Dataset $record): string => Str::limit($record->description ?? '', 50)),
                    
                Tables\Columns\TextColumn::make('organization.name')
                    ->label('Organization')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('sector.name')
                    ->label('Sector')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('year')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('file_name')
                    ->label('File Name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('file_type')
                    ->label('File Type')
                    ->formatStateUsing(fn (string $state): string => self::formatFileType($state))
                    ->badge()
                    ->color(fn (string $state): string => self::getFileTypeColor($state)),
                    
                Tables\Columns\TextColumn::make('file_size')
                    ->label('File Size'),
                    
                Tables\Columns\TextColumn::make('file_path')
                    ->label('Download')
                    ->formatStateUsing(fn ($state): string => 'Download File')
                    ->url(fn (Dataset $record): string => asset('storage/'.$record->file_path))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary'),
                    
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean()
                    ->label('Public')
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->label('Featured')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('published_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->options(function () {
                        $years = Dataset::select('year')
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year', 'year')
                            ->toArray();
                            
                        return array_filter($years);
                    })
                    ->label('Filter by Year'),
                    
                Tables\Filters\SelectFilter::make('organization_id')
                    ->relationship('organization', 'name')
                    ->label('Organization'),
                    
                Tables\Filters\SelectFilter::make('sector_id')
                    ->relationship('sector', 'name')
                    ->label('Sector'),
                    
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public Status'),
                    
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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