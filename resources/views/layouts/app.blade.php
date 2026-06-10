<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'IMM Content Planner')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
    <script>document.documentElement.dataset.theme = localStorage.getItem('imm-theme') || 'dark';</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-icons />
    @if (session('success') || session('status') || $errors->any())
        <div class="flash-stack" aria-live="polite" aria-atomic="false">
            @if (session('success'))
                <x-toast :message="session('success')" />
            @endif
            @if (session('status'))
                <x-toast :message="session('status')" />
            @endif
            @if ($errors->any())
                <x-toast type="error" :message="$errors->first()" :duration="7000" />
            @endif
        </div>
    @endif
    @yield('content')
</body>
</html>
