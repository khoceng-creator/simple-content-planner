@extends('layouts.app')

@section('title', "Preview {$contentPlan->headline}")

@section('content')
<div class="wrap preview-page">
    <header class="page-head">
        <div>
            <a class="btn back-link" href="{{ route('brands.workspace', ['brand' => $contentPlan->brand, 'year' => $contentPlan->posting_date->year, 'month' => $contentPlan->posting_date->month]) }}">
                <span class="icon"><svg><use href="#i-arrow-left"/></svg></span>Workspace
            </a>
        </div>
        <div class="head-actions">
            <x-theme-toggle />
            <a class="btn" href="{{ route('contents.pdf.preview', $contentPlan) }}" target="_blank" rel="noopener">
                <span class="icon"><svg><use href="#i-file-pdf"/></svg></span>Preview PDF
            </a>
            <a class="btn primary" href="{{ route('contents.pdf.download', $contentPlan) }}">
                <span class="icon"><svg><use href="#i-download"/></svg></span>Download PDF
            </a>
        </div>
    </header>
    <article class="preview-card">
        <section class="preview-hero">
            <div>
                <div class="preview-kicker">Jadwal konten · {{ $contentPlan->brand->name }}</div>
                <h1 class="preview-heading">{{ $contentPlan->headline }}</h1>
                <p class="preview-schedule">{{ $contentPlan->formatted_schedule }}</p>
                <div class="preview-top">
                    <span class="badge">{{ $contentPlan->type_label }}</span>
                    <span class="badge {{ $contentPlan->is_made ? 'done' : '' }}">{{ $contentPlan->is_made ? 'Sudah dibuat' : 'Belum dibuat' }}</span>
                    @if (($contentPlan->platforms['instagram'] ?? false)) <span class="platform-logo" title="Instagram" aria-label="Instagram"><svg><use href="#i-instagram"/></svg></span> @endif
                    @if (($contentPlan->platforms['tiktok'] ?? false)) <span class="platform-logo" title="TikTok" aria-label="TikTok"><svg><use href="#i-tiktok"/></svg></span> @endif
                </div>
            </div>
            <div class="preview-date-block" aria-label="Tanggal posting">
                <span>{{ $contentPlan->posting_date->locale('id')->translatedFormat('D') }}</span>
                <strong>{{ $contentPlan->posting_date->format('d') }}</strong>
                <small>{{ $contentPlan->posting_date->locale('id')->translatedFormat('M Y') }}</small>
            </div>
        </section>

        <div class="preview-summary-grid">
            <div class="preview-summary-card">
                <span>Waktu posting</span>
                <strong>{{ $contentPlan->posting_time ? substr((string) $contentPlan->posting_time, 0, 5) : 'Belum ditentukan' }}</strong>
            </div>
            <div class="preview-summary-card">
                <span>Media pendukung</span>
                <strong>{{ $contentPlan->images->count() }} gambar</strong>
            </div>
            <div class="preview-summary-card">
                <span>Dokumen</span>
                <strong>{{ $contentPlan->document_link ? 'Tersedia' : 'Belum tersedia' }}</strong>
            </div>
        </div>

        <section class="preview-section">
            <div class="preview-section-head"><span>01</span><h2>Detail / script</h2></div>
            <div class="preview-box rich-content">{!! $contentPlan->detail_html ?: '<span class="muted">Belum ada detail atau script.</span>' !!}</div>
        </section>
        <section class="preview-section">
            <div class="preview-section-head"><span>02</span><h2>Catatan produksi</h2></div>
            <div class="preview-box rich-content">{!! $contentPlan->note_html ?: '<span class="muted">Belum ada catatan produksi.</span>' !!}</div>
        </section>
        <section class="preview-section">
            <div class="preview-section-head">
                <span>03</span>
                <h2>Media pendukung</h2>
                @if ($contentPlan->images->isNotEmpty()) <small>{{ $contentPlan->images->count() }} file</small> @endif
            </div>
            @if ($contentPlan->images->isNotEmpty())
                <div class="preview-files">
                    @foreach ($contentPlan->images as $image)
                        <figure class="preview-file">
                            <img src="{{ $image->displayUrl() }}" alt="{{ $image->original_name }}">
                            <figcaption>{{ $image->original_name }}</figcaption>
                            <a class="btn preview-file-download" href="{{ $image->displayUrl() }}" download="{{ $image->original_name }}">
                                <span class="icon"><svg><use href="#i-download"/></svg></span>Download
                            </a>
                        </figure>
                    @endforeach
                </div>
            @else
                <div class="preview-box muted">Belum ada gambar.</div>
            @endif
        </section>
        <section class="preview-section">
            <div class="preview-section-head"><span>04</span><h2>Link dokumen</h2></div>
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
