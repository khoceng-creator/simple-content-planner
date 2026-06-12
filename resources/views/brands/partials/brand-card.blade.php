<article class="brand-card">
    <a class="brand-main" href="{{ route('brands.workspace', $brand) }}">
        @if ($brand->logo_path)
            <span class="logo has-image"><img src="{{ $brand->logoUrl() }}" alt="Logo {{ $brand->name }}" width="44" height="44" loading="lazy" decoding="async"></span>
        @else
            <span class="logo">{{ $brand->initials() }}</span>
        @endif
        <span>
            <span class="brand-name">{{ $brand->name }}</span>
            <span class="brand-meta">{{ $brand->content_plans_count }} konten tersimpan</span>
        </span>
    </a>
    <div class="brand-footer">
        <a class="btn brand-open-btn" href="{{ route('brands.workspace', $brand) }}">
            <span class="icon"><svg><use href="#i-note"/></svg></span>Buka
        </a>
        <span class="mini-actions">
            <button class="btn icon-only" type="button" aria-label="Edit {{ $brand->name }}"
                data-open-modal="brand-modal"
                data-brand="{{ json_encode(['id' => $brand->id, 'name' => $brand->name, 'logo_url' => $brand->logoUrl(), 'update_url' => route('brands.update', $brand)]) }}">
                <span class="icon"><svg><use href="#i-edit"/></svg></span>
            </button>
            <form method="POST" action="{{ route('brands.destroy', $brand) }}" data-confirm="Hapus brand {{ $brand->name }} beserta semua kontennya?">
                @csrf @method('DELETE')
                <button class="btn icon-only danger" type="submit" aria-label="Hapus {{ $brand->name }}">
                    <span class="icon"><svg><use href="#i-trash"/></svg></span>
                </button>
            </form>
        </span>
    </div>
</article>
