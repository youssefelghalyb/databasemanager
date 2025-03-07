<x-layouts.app>
    <div class="container mx-auto py-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Table Structure: {{ $table }}</h1>
                <a href="{{ url()->previous() }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Back
                </a>
            </div>
    
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Column Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Length</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nullable</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Default</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Extra</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($columns as $column)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $column->getName() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $column->getType()->getName() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $column->getLength() ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($column->getNotnull())
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                No
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Yes
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $column->getDefault() ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($column->getAutoincrement())
                                            <span class="text-xs text-gray-500">Auto Increment</span>
                                        @endif
                                        @if($column->getPrecision())
                                            <span class="text-xs text-gray-500">
                                                Precision: {{ $column->getPrecision() }}
                                                @if($column->getScale())
                                                    , Scale: {{ $column->getScale() }}
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>