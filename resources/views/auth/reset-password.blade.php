@extends('layouts.guest')

@section('title', 'Password Baru')

@section('content')
    <h1 class="login-title">Password baru</h1>
    <p class="login-subtitle">Gunakan password baru yang kuat untuk akun Anda.</p>
    @if ($errors->any()) <div class="login-error">{{ $errors->first() }}</div> @endif
    <form class="login-form" method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required>
        <label for="password">Password baru</label>
        <input id="password" name="password" type="password" required>
        <label for="password_confirmation">Ulangi password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required>
        <button class="btn primary" type="submit">Simpan password</button>
    </form>
@endsection
