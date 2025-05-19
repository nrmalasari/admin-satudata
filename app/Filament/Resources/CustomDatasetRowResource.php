<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomDatasetRowResource\Pages;
use App\Models\CustomDatasetColumn;
use App\Models\CustomDatasetRow;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CustomDatasetRowResource extends Resource
{
    protected static ?string $model = CustomDatasetRow::class;
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationLabel = 'Data Kustom';
    protected static ?string $pluralLabel = 'Data Kustom';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('custom_dataset_table_id')
                    ->label('Tabel Dataset')
                    ->relationship('customDatasetTable', 'title')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('data', []);
                    }),
                
                Forms\Components\Fieldset::make('Data Row')
                    ->schema(function (Forms\Get $get) {
                        $tableId = $get('custom_dataset_table_id');
                        
                        if (!$tableId) {
                            return [];
                        }
                        
                        $columns = CustomDatasetColumn::where('custom_dataset_table_id', $tableId)
                            ->orderBy('order_index')
                            ->get();
                        
                        $fields = [];
                        
                        foreach ($columns as $column) {
                            $field = match ($column->type) {
                                'integer' => Forms\Components\TextInput::make("data.{$column->name}")
                                    ->label($column->header)
                                    ->numeric()
                                    ->required(),
                                'float' => Forms\Components\TextInput::make("data.{$column->name}")
                                    ->label($column->header)
                                    ->numeric()
                                    ->step(0.01)
                                    ->required(),
                                'date' => Forms\Components\DatePicker::make("data.{$column->name}")
                                    ->label($column->header)
                                    ->required(),
                                default => Forms\Components\TextInput::make("data.{$column->name}")
                                    ->label($column->header)
                                    ->required(),
                            };
                            
                            $fields[] = $field;
                        }
                        
                        return $fields;
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::generateTableColumns())
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                
                Tables\Actions\Action::make('selectTable')
                    ->form([
                        Forms\Components\Select::make('table_id')
                            ->label('Pilih Tabel')
                            ->options(fn () => \App\Models\CustomDatasetTable::pluck('title', 'id'))
                            ->required()
                            ->searchable()
                    ])
                    ->action(function (array $data) {
                        return redirect()->to(self::getUrl('index', [
                            'table_id' => $data['table_id']
                        ]));
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected static function generateTableColumns(): array
    {
        $tableId = request()->query('table_id');
        
        if (!$tableId) {
            return [
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('customDatasetTable.title')
                    ->label('Tabel')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
            ];
        }

        $columns = CustomDatasetColumn::where('custom_dataset_table_id', $tableId)
            ->where('visible', true)
            ->orderBy('order_index')
            ->get();

        $tableColumns = [
            Tables\Columns\TextColumn::make('index')
                ->label('No')
                ->rowIndex(),
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];

        foreach ($columns as $column) {
            $tableColumns[] = Tables\Columns\TextColumn::make("data.{$column->name}")
                ->label($column->header)
                ->searchable()
                ->sortable()
                ->formatStateUsing(function ($state) use ($column) {
                    if (is_null($state)) {
                        return '-';
                    }

                    try {
                        if ($column->type === 'integer') {
                            return number_format(intval($state), 0);
                        }
                        if ($column->type === 'float') {
                            return number_format(floatval($state), 2);
                        }
                        if ($column->type === 'date') {
                            return Carbon::parse($state)->format('d/m/Y');
                        }
                        return Str::limit(strval($state), 50);
                    } catch (\Exception $e) {
                        return $state;
                    }
                });
        }

        $tableColumns[] = Tables\Columns\TextColumn::make('updated_at')
            ->label('Diperbarui')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        return $tableColumns;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomDatasetRows::route('/'),
            'create' => Pages\CreateCustomDatasetRow::route('/create'),
            'edit' => Pages\EditCustomDatasetRow::route('/{record}/edit'),
        ];
    }
}