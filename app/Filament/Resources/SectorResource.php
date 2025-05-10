<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectorResource\Pages;
use App\Filament\Resources\SectorResource\RelationManagers;
use App\Models\Sector;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SectorResource extends Resource
{
    protected static ?string $model = Sector::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $modelLabel = 'Sektor';
    protected static ?string $pluralModelLabel = 'Sektor';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Sektor')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Sektor')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state))),
                        
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->disabled(fn ($operation) => $operation === 'edit'),
                ])->columns(2),
                
            Forms\Components\Section::make('Icon Sektor')
                ->schema([
                    Forms\Components\FileUpload::make('icon')
                        ->label('Upload Icon')
                        ->required()
                        ->image()
                        ->directory('sector-icons') // Simpan di storage/app/public/sector-icons
                        ->disk('public') // Gunakan disk public
                        ->maxSize(2048) // 2MB
                        ->imageResizeMode('cover')
                        ->imageResizeTargetWidth('100')
                        ->imageResizeTargetHeight('100')
                        ->imagePreviewHeight('100')
                        ->getUploadedFileNameForStorageUsing(
                            fn (TemporaryUploadedFile $file): string => 'sector_icon_' . Str::random(10) . '.' . $file->getClientOriginalExtension()
                        )
                        ->uploadingMessage('Sedang mengunggah icon...')
                        ->uploadProgressIndicatorPosition('right')
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                        ->hint('Format: PNG/JPG/JPEG, Maksimal: 2MB')
                        ->hintIcon('heroicon-o-information-circle'),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('icon_url')
                    ->label('Icon')
                    ->getStateUsing(fn ($record) => $record->icon_url)
                    ->height(50)
                    ->width(50)
                    ->grow(false),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Sektor')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('organizations_count')
                    ->counts('organizations')
                    ->label('Jumlah Organisasi')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrganizationsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSectors::route('/'),
            'create' => Pages\CreateSector::route('/create'),
            'edit' => Pages\EditSector::route('/{record}/edit'),
        ];
    }
}