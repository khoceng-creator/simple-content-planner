<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') · IMM Content Planner</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
    <script>document.documentElement.dataset.theme = localStorage.getItem('imm-theme') || 'dark';</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-icons />
    <main class="login-wrap">
        <section class="login-card">
            <img class="login-logo" src="{{ asset('images/imm-logo.png') }}" alt="IMM">
            @yield('content')
        </section>
    </main>
</body>
</html>
