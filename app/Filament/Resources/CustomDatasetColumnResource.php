<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomDatasetColumnResource\Pages;
use App\Models\CustomDatasetColumn;
use App\Models\CustomDatasetTable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomDatasetColumnResource extends Resource
{
    protected static ?string $model = CustomDatasetColumn::class;
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Kolom Kustom';
    protected static ?string $pluralLabel = 'Kolom Kustom';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('custom_dataset_table_id')
                    ->label('Tabel Dataset')
                    ->relationship(
                        name: 'customDatasetTable', 
                        titleAttribute: 'title', // Menggunakan 'title' sesuai migrasi
                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('title')
                    )
                    ->required()
                    ->default(fn () => request()?->query('table_id'))
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('name')
                    ->label('Nama Kolom')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('header')
                    ->label('Judul Kolom')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('Tipe Data')
                    ->options([
                        'string' => 'Teks',
                        'integer' => 'Angka Bulat',
                        'float' => 'Angka Desimal',
                        'date' => 'Tanggal'
                    ])
                    ->default('string')
                    ->required(),

                Forms\Components\Toggle::make('visible')
                    ->label('Tampilkan Kolom')
                    ->default(true),

                Forms\Components\TextInput::make('order_index')
                    ->label('Urutan Kolom')
                    ->numeric()
                    ->default(0),

                Forms\Components\Select::make('filter_type')
                    ->label('Tipe Filter')
                    ->options([
                        'text' => 'Teks',
                        'number' => 'Angka',
                        'select' => 'Pilihan'
                    ])
                    ->default('text'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customDatasetTable.title') // Menggunakan 'title' sesuai migrasi
                    ->label('Tabel')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kolom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('header')
                    ->label('Judul Kolom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe Data'),
                Tables\Columns\BooleanColumn::make('visible')
                    ->label('Tampilkan'),
                Tables\Columns\TextColumn::make('order_index')
                    ->label('Urutan'),
                Tables\Columns\TextColumn::make('filter_type')
                    ->label('Tipe Filter'),
            ])
            ->filters([
                // Tambahkan filter jika diperlukan
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomDatasetColumns::route('/'),
            'create' => Pages\CreateCustomDatasetColumn::route('/create'),
            'edit' => Pages\EditCustomDatasetColumn::route('/{record}/edit'),
        ];
    }
}