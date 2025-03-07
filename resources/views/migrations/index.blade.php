@extends('databasemanager::layout.main')
    
@section('content')

<div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
    <h2 class="text-2xl font-bold text-gray-700 mb-4">Database Management</h2>
    
    @if(session('message'))
        <div class="mb-4 p-4 text-white bg-blue-500 rounded-lg">
            {{ session('message') }}
        </div>
    @endif
    
    <div class="overflow-x-auto">
        <table class="w-full table-auto border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 px-4 py-2 text-left">Module</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Connection</th>
                    <th class="border border-gray-300 px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($databases as $db)
                    <tr class="border border-gray-300 hover:bg-gray-100">
                        <td class="border border-gray-300 px-4 py-2">{{ $db->module_name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $db->connection_name }}</td>
                        <td class="border border-gray-300 px-4 py-2 text-center">
                            <a href="{{ route('database.migrate', $db->module_name) }}" class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">Migrate</a>
                            <a href="{{ route('database.rollback', $db->module_name) }}" class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">Rollback</a>
                            <a href="{{ route('database.status', $db->module_name) }}" class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">Status</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection