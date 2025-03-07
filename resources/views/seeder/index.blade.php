
@extends('databasemanager::layout.main')
    
@section('content')
   <div class="container mx-auto py-6">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Seeder Management</h1>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('seeder.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Module</label>
                        <select name="module" id="module" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Module</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module->getName() }}">{{ $module->getName() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Table</label>
                        <select name="table" id="table" class="w-full border rounded px-3 py-2" required>
                            <option value="">Select Table</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2">Seeder Name</label>
                        <input type="text" name="seeder_name" class="w-full border rounded px-3 py-2" required>
                    </div>
                </div>

                <div id="columns-container"></div>

                <div>
                    <label class="block text-sm font-medium mb-2">Number of Rows</label>
                    <input type="number" name="rows" class="w-full border rounded px-3 py-2" required
                        min="1">
                </div>

                <div class="flex justify-end mt-4 space-x-4">
                    <button type="button" id="preview-seeder"
                        class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Preview Seeder
                    </button>
                    <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                        Generate Seeder
                    </button>
                </div>

                <!-- Preview Modal -->
                <div id="seeder-preview-modal"
                    class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
                    <div class="bg-white p-6 rounded-lg w-2/3">
                        <h2 class="text-xl font-bold mb-4">Seeder Preview</h2>
                        <pre id="seeder-preview" class="p-4 bg-gray-200 rounded text-sm"></pre>
                        <div class="mt-4 flex justify-end">
                            <button id="close-preview" class="px-4 py-2 bg-red-500 text-white rounded">Close</button>
                        </div>
                    </div>
                </div>

        </div>
        </form>
    </div>
    </div>

        <script>
            document.getElementById('module').addEventListener('change', function() {
                let moduleName = this.value;
                if (!moduleName) return;

                try {
                    fetch(`http://localhost/Narzin/public/database-designer/seeder/tables/${moduleName}`)
                        .then(response => response.json())
                        .then(data => {
                            let tableSelect = document.getElementById('table');
                            tableSelect.innerHTML = '<option value="">Select Table</option>';

                            // Check if data is an array
                            if (Array.isArray(data)) {
                                data.forEach(table => {
                                    // Check if table is a string or an object
                                    const tableName = typeof table === 'string' ? table : (table.name ||
                                        JSON.stringify(table));
                                    const tableValue = typeof table === 'string' ? table : (table.value ||
                                        table.name || JSON.stringify(table));

                                    tableSelect.innerHTML +=
                                        `<option value="${tableValue}">${tableName}</option>`;
                                });
                            } else if (typeof data === 'object') {
                                // If data is an object, loop through its properties
                                Object.keys(data).forEach(key => {
                                    const tableName = data[key];
                                    tableSelect.innerHTML += `<option value="${key}">${tableName}</option>`;
                                });
                            }
                        })
                        .catch(error => console.error('Error fetching tables:', error));
                } catch (error) {
                    console.error('Unexpected error:', error);
                }
            });

            document.getElementById('table').addEventListener('change', function() {
                let moduleName = document.getElementById('module').value;
                let tableName = this.value;
                if (!moduleName || !tableName) return;

                fetch(
                        `http://localhost/Narzin/public/database-designer/seeder/columns/${moduleName}/${tableName}`
                    )
                    .then(response => response.json())
                    .then(data => {
                        let container = document.getElementById('columns-container');
                        container.innerHTML = '';

                        const fakerMethods = [
                            "name", "email", "text", "randomDigit", "date", "boolean", "word", "paragraph",
                            "uuid"
                        ];

                        Object.keys(data).forEach(column => {
                            if (column === 'id') return; // Skip ID columns

                            let options = `
                    <option value="faker">Faker Method</option>
                    <option value="custom">Custom Value</option>
                    <option value="random_number">Random Number</option>
                    <option value="foreign_key">Foreign Key</option>
                `;

                            container.innerHTML += `
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">${column}</label>
                        <select name="columns[${column}][type]" class="column-type w-full border rounded px-3 py-2" data-column="${column}">
                            ${options}
                        </select>
                        <div class="column-value-${column} mt-2"></div>
                    </div>
                `;
                        });



                        document.querySelectorAll('.column-type').forEach(select => {
    select.addEventListener('change', function() {
        let column = this.dataset.column;
        let container = document.querySelector(`.column-value-${column}`);
        container.innerHTML = '';

        if (this.value === 'faker') {
            let fakerOptions = fakerMethods.map(method =>
                `<option value="${method}">${method}</option>`
            ).join('');
            container.innerHTML =
                `<select name="columns[${column}][value]" class="w-full border rounded px-3 py-2">${fakerOptions}</select>`;
        } else if (this.value === 'custom') {
            container.innerHTML =
                `<input type="text" name="columns[${column}][value]" class="w-full border rounded px-3 py-2" placeholder="Enter custom value">`;
        } else if (this.value === 'random_number') {
            container.innerHTML = `
                <div class="flex space-x-2">
                    <input type="number" name="columns[${column}][min]" class="w-1/2 border rounded px-3 py-2" placeholder="Min">
                    <input type="number" name="columns[${column}][max]" class="w-1/2 border rounded px-3 py-2" placeholder="Max">
                </div>
            `;
        } else if (this.value === 'foreign_key') {
            // First create the container with the fields we need
            container.innerHTML = `
                <div class="foreign-key-container-${column}">
                    <select name="columns[${column}][module]" class="foreign-module w-full border rounded px-3 py-2 mb-2" data-column="${column}">
                        <option value="">Select Module</option>
                    </select>
                    <div class="foreign-table-container-${column} mt-2">
                        <select name="columns[${column}][table]" class="foreign-table w-full border rounded px-3 py-2" data-column="${column}" disabled>
                            <option value="">Select Table</option>
                        </select>
                    </div>
                    <div class="foreign-column-container-${column} mt-2">
                        <select name="columns[${column}][column]" class="foreign-column w-full border rounded px-3 py-2" data-column="${column}" disabled>
                            <option value="">Select Column</option>
                        </select>
                    </div>
                </div>
            `;

            // Then fetch the modules
            fetch('http://localhost/Narzin/public/database-designer/seeder/modules')
                .then(response => response.json())
                .then(modules => {
                    let moduleSelect = document.querySelector(`.foreign-module[data-column="${column}"]`);
                    
                    if (!moduleSelect) {
                        console.error(`Module select element not found for column ${column}`);
                        return;
                    }
                    
                    // First option is already there
                    modules.forEach(module => {
                        const option = document.createElement('option');
                        option.value = module;
                        option.textContent = module;
                        moduleSelect.appendChild(option);
                    });

                    // Add change event listener to the module select
                    moduleSelect.addEventListener('change', function() {
                        let selectedModule = this.value;
                        if (!selectedModule) return;

                        let tableSelect = document.querySelector(`.foreign-table[data-column="${column}"]`);
                        let columnSelect = document.querySelector(`.foreign-column[data-column="${column}"]`);
                        
                        if (!tableSelect) {
                            console.error(`Table select element not found for column ${column}`);
                            return;
                        }
                        
                        // Reset the table select
                        tableSelect.innerHTML = '<option value="">Select Table</option>';
                        tableSelect.disabled = true;
                        
                        // Reset the column select if it exists
                        if (columnSelect) {
                            columnSelect.innerHTML = '<option value="">Select Column</option>';
                            columnSelect.disabled = true;
                        }

                        // Fetch tables for the selected module
                        fetch(`http://localhost/Narzin/public/database-designer/seeder/tables/${selectedModule}`)
                            .then(response => response.json())
                            .then(tables => {
                                // Enable the table select
                                tableSelect.disabled = false;
                                
                                // Handle different response formats
                                if (Array.isArray(tables)) {
                                    tables.forEach(table => {
                                        const tableName = typeof table === 'string' ? table : (table.name || table);
                                        const tableValue = typeof table === 'string' ? table : (table.value || table.name || table);
                                        
                                        const option = document.createElement('option');
                                        option.value = tableValue;
                                        option.textContent = tableName;
                                        tableSelect.appendChild(option);
                                    });
                                } else if (typeof tables === 'object') {
                                    // If tables is an object, loop through its properties
                                    Object.keys(tables).forEach(key => {
                                        const tableName = tables[key];
                                        
                                        const option = document.createElement('option');
                                        option.value = key;
                                        option.textContent = tableName;
                                        tableSelect.appendChild(option);
                                    });
                                }
                                
                                // Add change event listener to the table select
                                tableSelect.addEventListener('change', function() {
                                    let selectedTable = this.value;
                                    if (!selectedTable) return;
                                    
                                    let columnSelect = document.querySelector(`.foreign-column[data-column="${column}"]`);
                                    
                                    if (!columnSelect) {
                                        console.error(`Column select element not found for column ${column}`);
                                        return;
                                    }
                                    
                                    // Reset the column select
                                    columnSelect.innerHTML = '<option value="">Select Column</option>';
                                    columnSelect.disabled = true;
                                    
                                    // Fetch columns for the selected table
                                    fetch(`http://localhost/Narzin/public/database-designer/seeder/columns/${selectedModule}/${selectedTable}`)
                                        .then(response => response.json())
                                        .then(columns => {
                                            if (typeof columns !== 'object') {
                                                console.error("Unexpected response format for columns:", columns);
                                                return;
                                            }
                                            
                                            // Enable the column select
                                            columnSelect.disabled = false;
                                            
                                            // Add options to the column select
                                            Object.keys(columns).forEach(col => {
                                                const option = document.createElement('option');
                                                option.value = col;
                                                option.textContent = col;
                                                columnSelect.appendChild(option);
                                            });
                                        })
                                        .catch(error => {
                                            console.error(`Error fetching columns for ${selectedModule}.${selectedTable}:`, error);
                                        });
                                });
                            })
                            .catch(error => {
                                console.error(`Error fetching tables for ${selectedModule}:`, error);
                            });
                    });
                })
                .catch(error => {
                    console.error('Error fetching modules:', error);
                });
        }
    });
});


                    });
            });




            document.getElementById('preview-seeder').addEventListener('click', function() {
                let form = new FormData(document.querySelector('form'));
                fetch('/seeder/preview', {
                        method: 'POST',
                        body: form
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('seeder-preview').textContent = data.preview;
                        document.getElementById('seeder-preview-modal').classList.remove('hidden');
                    });
            });

            document.getElementById('close-preview').addEventListener('click', function() {
                document.getElementById('seeder-preview-modal').classList.add('hidden');
            });
        </script>

@endsection
