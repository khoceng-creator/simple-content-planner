@php
    $snippet = \Illuminate\Support\Str::limit(strip_tags($plan->detail_html ?: $plan->note_html ?: ''), 130);
@endphp
<article class="feed-item">
    <div class="feed-top-actions">
        @php
            $typeIcon = match ($plan->type) {
                'single' => 'single-post',
                'carousel', 'reels' => $plan->type,
                default => 'note',
            };
        @endphp
        <span class="feed-type-icon"><svg><use href="#i-{{ $typeIcon }}"/></svg></span>
        <form method="POST" action="{{ route('contents.toggle-made', $plan) }}">
            @csrf @method('PATCH')
            <button class="feed-check-toggle {{ $plan->is_made ? 'active' : '' }}" type="submit" aria-label="Ubah status dibuat">
                <svg><use href="#i-check"/></svg>
            </button>
        </form>
    </div>
    <a class="feed-eye" href="{{ route('contents.preview', $plan) }}" aria-label="Preview {{ $plan->headline }}"><span><svg><use href="#i-eye"/></svg></span></a>
    <div class="feed-info">
        <div class="feed-title">{{ $plan->headline }}</div>
        <div class="feed-date">{{ $plan->posting_date->format('d/m') }}</div>
        <div class="feed-snippet">{{ $snippet ?: 'Belum ada detail' }}</div>
        <div class="feed-bottom">
            <span class="badge">{{ $contentTypeLabels[$plan->type] ?? $plan->type_label }}</span>
            <span class="badge">{{ $plan->posting_date->format('d/m') }}</span>
            @if ($plan->is_made)<span class="badge done"><span class="icon"><svg><use href="#i-check"/></svg></span>Dibuat</span>@endif
        </div>
    </div>
</article>
