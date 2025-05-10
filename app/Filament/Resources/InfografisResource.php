<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InfografisResource\Pages;
use App\Models\Infografis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Database\Eloquent\Builder;

class InfografisResource extends Resource
{
    protected static ?string $model = Infografis::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Konten';
    protected static ?string $modelLabel = 'Infografis';
    protected static ?string $pluralModelLabel = 'Infografis';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Infografis')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label('Judul')
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
                        
                    Forms\Components\Select::make('sector_id')
                        ->label('Sektor')
                        ->relationship('sector', 'name')
                        ->required(),
                        
                    Forms\Components\DatePicker::make('published_date')
                        ->label('Tanggal Publikasi')
                        ->required()
                        ->default(now()),
                ])->columns(2),
                
            Forms\Components\Section::make('Gambar Infografis')
                ->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Upload Gambar')
                        ->required()
                        ->image()
                        ->directory('infografis') // Simpan di storage/app/public/infografis
                        ->disk('public') // Gunakan disk public
                        ->maxSize(2048) // 2MB
                        ->imageResizeMode('cover')
                        ->imageResizeTargetWidth('800')
                        ->imageResizeTargetHeight('600')
                        ->imagePreviewHeight('200')
                        ->getUploadedFileNameForStorageUsing(
                            fn (TemporaryUploadedFile $file): string => 'infografis_' . Str::random(10) . '.' . $file->getClientOriginalExtension()
                        )
                        ->uploadingMessage('Sedang mengunggah gambar...')
                        ->uploadProgressIndicatorPosition('right')
                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                        ->hint('Format: PNG/JPG/JPEG, Maksimal: 2MB')
                        ->hintIcon('heroicon-o-information-circle'),
                ]),
                
            Forms\Components\Section::make('Status')
                ->schema([
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Ditampilkan')
                        ->default(false),
                        
                    Forms\Components\Toggle::make('is_published')
                        ->label('Dipublikasikan')
                        ->default(true),
                        
                    Forms\Components\TextInput::make('views')
                        ->label('Jumlah Dilihat')
                        ->numeric()
                        ->default(0)
                        ->disabled(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Gambar')
                    ->getStateUsing(fn ($record) => $record->image_url)
                    ->height(50)
                    ->width(50)
                    ->grow(false),
                    
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn (string $state): ?string => strlen($state) > 30 ? $state : null),
                    
                Tables\Columns\TextColumn::make('sector.name')
                    ->label('Sektor')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('published_date')
                    ->label('Tanggal Publikasi')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Ditampilkan')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Dipublikasikan')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('views')
                    ->label('Dilihat')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sector')
                    ->relationship('sector', 'name')
                    ->label('Sektor'),
                    
                Tables\Filters\Filter::make('featured')
                    ->query(fn (Builder $query) => $query->where('is_featured', true))
                    ->label('Hanya Ditampilkan'),
                    
                Tables\Filters\Filter::make('published')
                    ->query(fn (Builder $query) => $query->where('is_published', true))
                    ->label('Hanya Dipublikasikan'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\ViewAction::make()
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInfografis::route('/'),
            'create' => Pages\CreateInfografis::route('/create'),
            'edit' => Pages\EditInfografis::route('/{record}/edit'),
        ];
    }
}