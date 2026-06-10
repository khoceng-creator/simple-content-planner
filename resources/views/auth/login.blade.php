@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <h1 class="login-title">IMM Content Planner</h1>
    <p class="login-subtitle">Masuk untuk mengelola workspace konten.</p>

    @if (session('status')) <div class="login-message">{{ session('status') }}</div> @endif
    @if ($errors->any()) <div class="login-error">{{ $errors->first() }}</div> @endif

    <form class="login-form" method="POST" action="{{ route('login') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <label class="remember"><input type="checkbox" name="remember" value="1"> Ingat saya</label>
        <button class="btn primary" type="submit">Login</button>
        <a class="text-link" href="{{ route('password.request') }}">Lupa password?</a>
    </form>
@endsection
