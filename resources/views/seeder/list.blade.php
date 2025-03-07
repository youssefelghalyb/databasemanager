
@extends('databasemanager::layout.main')
    
@section('content')
    <div class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg">
        <h2 class="text-2xl font-semibold mb-4">Manage Seeders</h2>
    
        <!-- Module Selection -->
        <form method="GET" class="mb-6">
            <label for="module" class="block text-sm font-medium text-gray-700">Select Module</label>
            <select name="module" id="module" onchange="this.form.submit()" 
                class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Select Module</option>
                @foreach ($modules as $module)
                    <option value="{{ $module->getName() }}" {{ $selectedModule == $module->getName() ? 'selected' : '' }}>
                        {{ $module->getName() }}
                    </option>
                @endforeach
            </select>
        </form>
    
        <!-- Seeder List -->
        @if ($seeders->isNotEmpty())
            <ul class="space-y-4">
                @foreach ($seeders as $seeder)
                    <li class="flex justify-between items-center bg-gray-100 p-4 rounded-lg shadow">
                        <span class="text-lg font-medium">{{ $seeder->seeder_name }}</span>
                        
                        <div class="flex space-x-3">
                            <!-- Run Seeder Button -->
                            <form action="{{ route('seeder.run', ['id' => $seeder->id, 'module' => $selectedModule]) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition">
                                    Run
                                </button>
                            </form>
    
                            <!-- Preview Seeder Button -->
                            <button onclick="previewSeeder('{{ $seeder->seeder_name }}', '{{ $selectedModule }}')" 
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition">
                                Preview
                            </button>
    
                            <!-- Delete Seeder Button -->
                            <button onclick="confirmDelete('{{ $seeder->seeder_name }}', '{{ $selectedModule }}')"
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md transition">
                                Delete
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-gray-500">No seeders found for this module.</p>
        @endif
    </div>
    
    <!-- Seeder Preview Modal -->
    <div id="seederPreviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-2xl w-full">
            <h3 class="text-xl font-semibold mb-4">Seeder Preview</h3>
            <pre id="seederContent" class="bg-gray-200 p-4 rounded overflow-x-auto max-h-80"></pre>
            <button onclick="closePreview()" class="mt-4 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
                Close
            </button>
        </div>
    </div>
    
    <script>
        function previewSeeder(seederName, moduleName) {
            if (!moduleName) {
                alert('Please select a module first.');
                return;
            }
    
             fetch(`{{ env('APP_URL') }}database-designer/seeder/preview/${moduleName}/${seederName}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('seederContent').textContent = data;
                    document.getElementById('seederPreviewModal').classList.remove('hidden');
                });
        }
    
        function closePreview() {
            document.getElementById('seederPreviewModal').classList.add('hidden');
        }
    
        function confirmDelete(seederName, moduleName) {
            if (confirm(`Are you sure you want to delete ${seederName}?`)) {
                fetch(`{{ env('APP_URL') }}database-designer/seeder/delete/${moduleName}/${seederName}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete the seeder.');
                });
            }
        }
    </script>
    
    @endsection