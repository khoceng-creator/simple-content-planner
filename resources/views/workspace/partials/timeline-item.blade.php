@php
    $payload = [
        'id' => $plan->id,
        'update_url' => route('contents.update', $plan),
        'posting_date' => $plan->posting_date->format('Y-m-d'),
        'posting_time' => $plan->posting_time ? substr($plan->posting_time, 0, 5) : '',
        'type' => $plan->type,
        'platforms' => array_merge(['instagram' => false, 'tiktok' => false], $plan->platforms ?? []),
        'headline' => $plan->headline,
        'detail_html' => $plan->detail_html,
        'note_html' => $plan->note_html,
        'document_link' => $plan->document_link,
        'images' => $plan->images->map(fn ($image) => ['id' => $image->id, 'name' => $image->original_name, 'url' => $image->displayUrl()])->values(),
    ];
    $platforms = array_merge(['instagram' => false, 'tiktok' => false], $plan->platforms ?? []);
@endphp
<div class="content-row">
    <div class="date-col">
        <div class="date-text">{{ $plan->posting_date->locale('id')->translatedFormat('D') }}<br>{{ $plan->posting_date->format('d/m') }}</div>
        <div class="dot"></div>
        @unless ($loop->last)<div class="line"></div>@endunless
    </div>
    <article class="content-card">
        <div class="content-card-top">
            <div class="headline">{{ $plan->headline }}</div>
            <div class="card-right">
                <div class="badges">
                    <span class="badge">{{ $contentTypeLabels[$plan->type] ?? $plan->type_label }}</span>
                    <span class="badge">{{ $plan->posting_time ? substr($plan->posting_time, 0, 5) : '--:--' }}</span>
                </div>
                <div class="platform-box">
                    @if ($platforms['instagram']) <span class="platform-logo" title="Instagram"><svg><use href="#i-instagram"/></svg></span> @endif
                    @if ($platforms['tiktok']) <span class="platform-logo" title="TikTok"><svg><use href="#i-tiktok"/></svg></span> @endif
                </div>
                <form method="POST" action="{{ route('contents.toggle-made', $plan) }}">
                    @csrf @method('PATCH')
                    <button class="status-toggle {{ $plan->is_made ? 'active' : '' }}" type="submit">
                        <span class="icon"><svg><use href="#i-check"/></svg></span>Dibuat
                    </button>
                </form>
            </div>
        </div>
        @if ($plan->detail_html)<div class="detail rich-content">{!! $plan->detail_html !!}</div>@endif
        @if ($plan->note_html)<div class="note"><span class="icon"><svg><use href="#i-note"/></svg></span><div class="rich-content">{!! $plan->note_html !!}</div></div>@endif
        @if ($plan->images->isNotEmpty() || $plan->document_link)
            <div class="attach-list">
                @if ($plan->images->isNotEmpty()) <span class="attach-link"><span class="icon"><svg><use href="#i-paperclip"/></svg></span>{{ $plan->images->count() }} gambar</span> @endif
                @if ($plan->document_link) <a class="attach-link" href="{{ $plan->document_link }}" target="_blank" rel="noopener noreferrer">Link dokumen</a> @endif
            </div>
        @endif
        <div class="card-actions">
            <a class="btn preview-btn" href="{{ route('contents.preview', $plan) }}"><span class="icon"><svg><use href="#i-eye"/></svg></span>Preview</a>
            <button class="btn" type="button" data-open-modal="content-modal" data-content="{{ json_encode($payload) }}">
                <span class="icon"><svg><use href="#i-edit"/></svg></span>Edit
            </button>
            <form method="POST" action="{{ route('contents.destroy', $plan) }}" data-confirm="Hapus konten ini?">
                @csrf @method('DELETE')
                <button class="btn danger" type="submit"><span class="icon"><svg><use href="#i-trash"/></svg></span>Hapus</button>
            </form>
        </div>
    </article>
</div>
