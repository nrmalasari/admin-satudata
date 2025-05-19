<x-filament::page>
    <x-filament::card>
        <div class="px-4 py-2">
            <h2 class="text-lg font-medium">{{ $record->title ?? 'Dataset Tanpa Judul' }}</h2>
            @if($record->description)
                <p class="text-sm text-gray-500">{{ $record->description }}</p>
            @endif
            <p class="text-sm text-gray-500">Organisasi: {{ $record->organization?->name ?? 'Tidak ada organisasi' }}</p>
            <p class="text-sm text-gray-500">Sektor: {{ $record->sector?->name ?? 'Tidak ada sektor' }}</p>
            <p class="text-sm text-gray-500">Tanggal Publikasi: {{ $record->published_date?->format('d/m/Y') ?? '-' }}</p>
        </div>

        @if($record->customDatasetTable)
            @php
                $columns = $record->customDatasetTable->columns()
                    ->where('visible', true)
                    ->orderBy('order_index')
                    ->get();
                    
                $rows = $record->customDatasetTable->rows()
                    ->limit(10)
                    ->get();
            @endphp

            @if($columns->isNotEmpty() && $rows->isNotEmpty())
                <div class="overflow-x-auto mt-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach($columns as $column)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $column->header ?? 'Kolom Tanpa Nama' }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($rows as $row)
                                <tr>
                                    @foreach($columns as $column)
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @php
                                                $value = $row->data[$column->name] ?? null;
                                            @endphp
                                            @if($column->type === 'date' && $value)
                                                {{ \Carbon\Carbon::parse($value)?->format('d/m/Y') ?? '-' }}
                                            @elseif(in_array($column->type, ['float', 'integer']))
                                                {{ number_format($value ?? 0, $column->type === 'float' ? 2 : 0) }}
                                            @else
                                                {{ $value ?? '-' }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-4 py-8 text-center text-gray-500">
                    <p>Tidak ada data untuk ditampilkan dari tabel sumber</p>
                </div>
            @endif
        @else
            <div class="px-4 py-8 text-center text-gray-500">
                <p>Dataset ini tidak terkait dengan tabel kustom</p>
                @if($record->file_path)
                    <a href="{{ asset('storage/'.$record->file_path) }}" 
                       target="_blank"
                       class="mt-2 inline-flex items-center text-primary-600 hover:text-primary-800">
                        Unduh file untuk melihat konten
                    </a>
                @endif
            </div>
        @endif
    </x-filament::card>
</x-filament::page>