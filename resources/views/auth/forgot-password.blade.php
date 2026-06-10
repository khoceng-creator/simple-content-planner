@extends('layouts.guest')

@section('title', 'Lupa Password')

@section('content')
    <h1 class="login-title">Reset password</h1>
    <p class="login-subtitle">Kami akan mengirimkan tautan reset ke email akun Anda.</p>
    @if (session('status')) <div class="login-message">{{ session('status') }}</div> @endif
    @if ($errors->any()) <div class="login-error">{{ $errors->first() }}</div> @endif
    <form class="login-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
        <button class="btn primary" type="submit">Kirim tautan reset</button>
        <a class="text-link" href="{{ route('login') }}">Kembali ke login</a>
    </form>
@endsection
