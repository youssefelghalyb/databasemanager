@extends('databasemanager::layout.main')
    
@section('content')
<div class="container">
    <h2>Migration Status for {{ $moduleName }}</h2>
    <pre>{{ $output }}</pre>
    <a href="{{ route('database.index') }}" class="btn btn-primary">Back</a>
</div>

@endsection
