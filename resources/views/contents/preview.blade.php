@extends('layouts.app')

@section('title', "Preview {$contentPlan->headline}")

@section('content')
<div class="wrap preview-page">
    <header class="page-head">
        <div>
            <a class="btn back-link" href="{{ route('brands.workspace', ['brand' => $contentPlan->brand, 'year' => $contentPlan->posting_date->year, 'month' => $contentPlan->posting_date->month]) }}">
                <span class="icon"><svg><use href="#i-arrow-left"/></svg></span>Workspace
            </a>
            <h1 class="page-title preview-heading">{{ $contentPlan->headline }}</h1>
            <p class="page-subtitle">{{ $contentPlan->brand->name }} · {{ $contentPlan->formatted_schedule }}</p>
        </div>
        <div class="head-actions">
            <x-theme-toggle />
            <a class="btn primary" href="{{ route('contents.print', $contentPlan) }}" target="_blank">
                <span class="icon"><svg><use href="#i-print"/></svg></span>Print / PDF
            </a>
            <button class="btn" type="button" data-share-summary="{{ $contentPlan->headline }}&#10;{{ $contentPlan->formatted_schedule }}&#10;{{ $contentPlan->document_link }}">Share</button>
        </div>
    </header>
    <article class="preview-card">
        <div class="preview-top">
            <span class="badge">{{ $contentPlan->type_label }}</span>
            <span class="badge {{ $contentPlan->is_made ? 'done' : '' }}">{{ $contentPlan->is_made ? 'Sudah dibuat' : 'Belum dibuat' }}</span>
            @if (($contentPlan->platforms['instagram'] ?? false)) <span class="platform-logo" title="Instagram"><svg><use href="#i-instagram"/></svg></span> @endif
            @if (($contentPlan->platforms['tiktok'] ?? false)) <span class="platform-logo" title="TikTok"><svg><use href="#i-tiktok"/></svg></span> @endif
        </div>
        <section class="preview-section"><h2>Detail / script</h2><div class="preview-box rich-content">{!! $contentPlan->detail_html ?: '<span class="muted">Belum ada detail.</span>' !!}</div></section>
        <section class="preview-section"><h2>Catatan</h2><div class="preview-box rich-content">{!! $contentPlan->note_html ?: '<span class="muted">Belum ada catatan.</span>' !!}</div></section>
        <section class="preview-section">
            <h2>Gambar</h2>
            @if ($contentPlan->images->isNotEmpty())
                <div class="preview-files">
                    @foreach ($contentPlan->images as $image)
                        <figure class="preview-file">
                            <img src="{{ $image->displayUrl() }}" alt="{{ $image->original_name }}">
                            <figcaption>{{ $image->original_name }}</figcaption>
                            <a class="btn" href="{{ $image->displayUrl() }}" download="{{ $image->original_name }}">Download</a>
                        </figure>
                    @endforeach
                </div>
            @else
                <div class="preview-box muted">Belum ada gambar.</div>
            @endif
        </section>
        <section class="preview-section">
            <h2>Link dokumen</h2>
            @if ($contentPlan->document_link)
                <div class="link-row">
                    <input value="{{ $contentPlan->document_link }}" readonly>
                    <button class="btn" type="button" data-copy="{{ $contentPlan->document_link }}">Copy</button>
                    <a class="btn" href="{{ $contentPlan->document_link }}" target="_blank" rel="noopener noreferrer">Open</a>
                </div>
            @else
                <div class="preview-box muted">Belum ada link dokumen.</div>
            @endif
        </section>
    </article>
</div>
@endsection
