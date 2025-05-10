<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $modelLabel = 'Organisasi';
    protected static ?string $navigationLabel = 'Organisasi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Organisasi')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Organisasi')
                        ->required()
                        ->maxLength(255),
                        
                    Forms\Components\Select::make('sector_id')
                        ->label('Sektor')
                        ->relationship('sector', 'name')
                        ->required(),
                        
                    Forms\Components\FileUpload::make('logo_path')
                        ->label('Logo')
                        ->directory('organizations')
                        ->image()
                        ->maxSize(2048),
                ])->columns(2),
                
            Forms\Components\Section::make('Deskripsi')
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi Organisasi')
                        ->columnSpanFull()
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Organisasi')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('sector.name')
                    ->label('Sektor')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('datasets_count')
                    ->label('Jumlah Dataset')
                    ->numeric()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('last_updated')
                    ->label('Update Terakhir')
                    ->dateTime('d F Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('viewDatasets')
                    ->label('Lihat Dataset')
                    ->url(fn (Organization $record) => DatasetResource::getUrl('index', ['organization_id' => $record->id]))
                    ->icon('heroicon-o-document-text'),
                Tables\Actions\Action::make('updateDatasets')
                    ->label('Update Dataset')
                    ->url(fn (Organization $record) => DatasetResource::getUrl('create', ['organization_id' => $record->id]))
                    ->icon('heroicon-o-plus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}