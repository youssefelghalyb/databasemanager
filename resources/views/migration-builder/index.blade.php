@extends('databasemanager::layout.main')
    
@section('content')

    <div class="container mx-auto py-6">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Migration Builder</h1>
    
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif
    

    
            <form action="{{ route('migration-builder.create') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Module</label>
                        <select name="module" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Module</option>
                            @foreach($modules as $module)
                                <option value="{{ $module->getName() }}">{{ $module->getName() }}</option>
                            @endforeach
                        </select>
                    </div>
    
                    <div>
                        <label class="block text-sm font-medium mb-2">Database Connection</label>
                        <select name="database" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Database</option>
                            @foreach($databases as $database)
                                <option value="{{ $database }}">{{ $database }}</option>
                            @endforeach
                        </select>
                    </div>
    
                    <div>
                        <label class="block text-sm font-medium mb-2">Table Name</label>
                        <input type="text" name="table_name" class="w-full border rounded px-3 py-2" required>
                    </div>
                </div>
    
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold">Columns</h2>
                        <button type="button" onclick="addColumn()" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Add Column
                        </button>
                    </div>
    
                    <div id="columns-container">
                        <!-- Column templates will be added here -->
                    </div>
                </div>
    
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                        Generate Migration
                    </button>
                </div>
            </form>
    
            <!-- Column Template (hidden) -->
            <template id="column-template">
                <div class="column-row grid grid-cols-1 md:grid-cols-5 gap-4 p-4 border rounded mb-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Name</label>
                        <input type="text" name="columns[{index}][name]" class="w-full border rounded px-3 py-2" required>
                    </div>
    
                    <div>
                        <label class="block text-sm font-medium mb-2">Type</label>
                        <select name="columns[{index}][type]" class="w-full border rounded px-3 py-2" required>
                            @foreach($columnTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
    
                    <div>
                        <label class="block text-sm font-medium mb-2">Length/Values</label>
                        <input type="text" name="columns[{index}][length]" class="w-full border rounded px-3 py-2">
                    </div>
    
                    <div>
                        <label class="block text-sm font-medium mb-2">Default Value</label>
                        <input type="text" name="columns[{index}][default]" class="w-full border rounded px-3 py-2">
                    </div>
    
                    <div class="flex items-end space-x-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="columns[{index}][nullable]" class="mr-2">
                            <label class="text-sm">Nullable</label>
                        </div>
                        <button type="button" onclick="removeColumn(this)" class="p-2 text-red-500 hover:text-red-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
    
    <script>
        let columnIndex = 0;
    
        function addColumn() {
            const template = document.getElementById('column-template');
            const container = document.getElementById('columns-container');
            const clone = template.content.cloneNode(true);
            
            // Replace {index} placeholder with actual index
            const elements = clone.querySelectorAll('[name*="{index}"]');
            elements.forEach(element => {
                element.name = element.name.replace('{index}', columnIndex);
            });
    
            container.appendChild(clone);
            columnIndex++;
        }
    
        function removeColumn(button) {
            const columnRow = button.closest('.column-row');
            columnRow.remove();
        }
    
        // Add first column by default
        document.addEventListener('DOMContentLoaded', function() {
            addColumn();
        });
    </script>
     
    @endsection