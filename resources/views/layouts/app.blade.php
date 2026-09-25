<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pelayanan Masyarakat Kecamatan Cikampek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/interactive-display.css') }}">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="text-gray-800 antialiased h-screen flex flex-col"
      data-session-success="{{ session('success') ?? session('status') }}"
      data-session-error="{{ session('error') }}"
      data-session-warning="{{ session('warning') }}"
      data-session-info="{{ session('info') }}">
    @yield('content')

    <script src="{{ asset('js/interactive-display.js') }}"></script>
    <x-chatbot />
</body>
</html>
