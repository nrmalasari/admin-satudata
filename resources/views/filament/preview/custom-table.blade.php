<x-filament::page>
    @php
        $record = $record ?? null;
        $columns = $columns ?? collect();
        $rows = $rows ?? collect();
        $hasData = $columns->isNotEmpty() && $rows->isNotEmpty();
    @endphp

    <x-filament::card>
        <!-- Header dengan informasi organisasi dan sektor -->
        <div class="px-4 py-3 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-lg font-semibold">
                        {{ $record->title ?? 'Preview Tabel' }}
                    </h2>
                    @if($record && ($record->organization || $record->sector))
                        <div class="flex gap-4 mt-1">
                            @if($record->organization)
                                <span class="text-sm text-gray-600">
                                    <span class="font-medium">Organisasi:</span> 
                                    {{ $record->organization->name }}
                                </span>
                            @endif
                            @if($record->sector)
                                <span class="text-sm text-gray-600">
                                    <span class="font-medium">Sektor:</span> 
                                    {{ $record->sector->name }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="text-sm text-gray-500">
                    Total Data: {{ $rows->count() }}
                </div>
            </div>
        </div>

        <!-- Tabel data -->
        @if($hasData)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @foreach($columns as $column)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ $column->header }}
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
                                        
                                        @switch($column->type)
                                            @case('date')
                                                {{ $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '-' }}
                                                @break
                                            @case('float')
                                                {{ number_format((float)$value, 2) }}
                                                @break
                                            @case('integer')
                                                {{ number_format((int)$value, 0) }}
                                                @break
                                            @default
                                                {{ $value ?? '-' }}
                                        @endswitch
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 text-center text-gray-500">
                Tidak ada data yang tersedia untuk ditampilkan
            </div>
        @endif

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 text-sm text-gray-500">
            <div class="flex justify-between items-center">
                <span>
                    Terakhir diperbarui: {{ $record->updated_at->format('d/m/Y H:i') ?? '-' }}
                </span>
                @if($record->is_public)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Publik
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Privat
                    </span>
                @endif
            </div>
        </div>
    </x-filament::card>
</x-filament::page>