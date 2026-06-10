<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') · IMM Content Planner</title>
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
