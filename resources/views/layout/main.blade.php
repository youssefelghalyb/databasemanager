<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Database Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">


    @if (session('success') || session('error'))
        @php
            $type = session('success') ? 'success' : 'error';
            $message = session('success') ?? session('error');
            $bgColor = $type === 'success' ? '#d4edda' : '#f8d7da';
            $textColor = $type === 'success' ? '#155724' : '#721c24';
        @endphp

        <div class="alert p-4 mb-4 rounded-lg" role="alert"
            style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
            {{ $message }}
        </div>
    @endif



    @yield('content')

</body>

</html>
