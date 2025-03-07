<x-layouts.app>
    <div class="container mx-auto py-6">
        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Database Tables</h1>
                <a href="{{ route('migration-builder.index') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Create New Migration
                </a>
            </div>
    
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
    
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif
    
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b">
                    <div class="flex space-x-4">
                        <select id="moduleSelect" class="border rounded px-3 py-2" onchange="updateTables()">
                            <option value="">Select Module</option>
                            @foreach($modules as $module)
                                <option value="{{ $module->getName() }}" @if($selectedModule === $module->getName()) selected @endif>
                                    {{ $module->getName() }}
                                </option>
                            @endforeach
                        </select>
    
                        <select id="databaseSelect" class="border rounded px-3 py-2" onchange="updateTables()">
                            <option value="">Select Database</option>
                            @foreach($databases as $database)
                                <option value="{{ $database }}" @if($selectedDatabase === $database) selected @endif>
                                    {{ $database }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
    
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Table Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Migration File</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($tables as $table)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $table['name'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($table['migrated'])
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Migrated
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $table['migration_file'] ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex space-x-2">
                                            @if(!$table['migrated'])
                                                <form action="{{ route('migration-builder.migrate') }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="module" value="{{ $selectedModule }}">
                                                    <input type="hidden" name="table" value="{{ $table['name'] }}">
                                                    <button type="submit" class="text-blue-600 hover:text-blue-900">Migrate</button>
                                                </form>
                                            @endif
                                            <a href="{{ route('migration-builder.structure', ['table' => $table['name']]) }}" 
                                               class="text-gray-600 hover:text-gray-900">
                                                View Structure
                                            </a>
                                            @if($table['migrated'])
                                                <form action="{{ route('migration-builder.rollback') }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Are you sure you want to rollback this migration?');">
                                                    @csrf
                                                    <input type="hidden" name="module" value="{{ $selectedModule }}">
                                                    <input type="hidden" name="table" value="{{ $table['name'] }}">
                                                    <button type="submit" class="text-red-600 hover:text-red-900">Rollback</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                        No tables found. Please select a module and database connection.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        function updateTables() {
            const module = document.getElementById('moduleSelect').value;
            const database = document.getElementById('databaseSelect').value;
            if (module && database) {
                window.location.href = `{{ route('migration-builder.tables') }}?module=${module}&database=${database}`;
            }
        }
    </script>
    @endpush
</x-layouts.app>