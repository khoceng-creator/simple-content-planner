@extends('layouts.app')

@section('title', "Content Planner {$brand->name}")

@section('content')
<div class="wrap workspace-wrap">
    <div class="planner-shell">
        @include('workspace.partials.sidebar')
        <main class="main">
            <header class="main-head">
                <div>
                    <h1 class="planner-title">Content Planner {{ $selectedMonth->locale('id')->translatedFormat('F Y') }}</h1>
                    <p class="planner-brand">{{ $brand->name }}</p>
                </div>
                <div class="head-actions">
                    <x-theme-toggle />
                    <button class="btn primary" type="button" data-open-modal="content-modal" data-new-content>
                        <span class="icon"><svg><use href="#i-plus"/></svg></span>Tambah
                    </button>
                </div>
            </header>
            <nav class="view-tabs">
                @foreach (['timeline' => 'Timeline', 'feed' => 'Feed'] as $value => $label)
                    <a class="view-tab {{ $view === $value ? 'active' : '' }}"
                       href="{{ route('brands.workspace', ['brand' => $brand, 'year' => $year, 'month' => $month, 'type' => $type, 'status' => $status, 'view' => $value]) }}">{{ $label }}</a>
                @endforeach
            </nav>
            <nav class="filters">
                <a class="chip {{ $type === 'semua' ? 'active' : '' }}"
                   href="{{ route('brands.workspace', ['brand' => $brand, 'year' => $year, 'month' => $month, 'type' => 'semua', 'status' => $status, 'view' => $view]) }}">Semua</a>
                @foreach ($contentTypes as $contentType)
                    <a class="chip {{ $type === $contentType->slug ? 'active' : '' }}"
                       href="{{ route('brands.workspace', ['brand' => $brand, 'year' => $year, 'month' => $month, 'type' => $contentType->slug, 'status' => $status, 'view' => $view]) }}">{{ $contentType->name }}</a>
                @endforeach
                @if ($status !== 'semua')
                    <span class="filter-state">
                        Status: {{ $status === 'dibuat' ? 'Sudah dibuat' : 'Belum dibuat' }}
                    </span>
                @endif
            </nav>
            @include("workspace.partials.{$view}")
        </main>
    </div>
</div>
@include('workspace.partials.content-form-modal')
@endsection
