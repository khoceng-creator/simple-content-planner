@extends('layouts.app')

@section('content')
<div class="wrap">
    <header class="page-head">
        <div>
            <h1 class="page-title">IMM Content Planner</h1>
            <p class="page-subtitle">Kelola lembar kerja konten setiap brand dalam satu workspace internal.</p>
        </div>
        <div class="head-actions">
            <x-theme-toggle />
            <button class="btn primary" type="button" data-open-modal="brand-modal" data-new-brand>
                <span class="icon"><svg><use href="#i-plus"/></svg></span>Tambah brand
            </button>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn" type="submit">Logout</button></form>
        </div>
    </header>

    <div class="brand-grid">
        @foreach ($brands as $brand)
            @include('brands.partials.brand-card', ['brand' => $brand])
        @endforeach
        <button class="add-card" type="button" data-open-modal="brand-modal" data-new-brand>
            <span class="add-plus">+</span><strong>Tambah brand</strong><span>Workspace konten baru</span>
        </button>
    </div>
</div>
@include('brands.partials.brand-form-modal')
@endsection
