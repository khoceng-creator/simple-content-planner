@php
    $previous = $selectedMonth->subMonth();
    $next = $selectedMonth->addMonth();
@endphp
<aside class="side">
    <div class="side-brand">
        <a class="btn" href="{{ route('brands.index') }}">
            <span class="icon"><svg><use href="#i-arrow-left"/></svg></span>Semua brand
        </a>
        <div class="brand-main side-brand-main">
            @if ($brand->logo_path)
                <span class="logo has-image"><img src="{{ $brand->logoUrl() }}" alt="Logo {{ $brand->name }}" width="44" height="44" decoding="async" fetchpriority="high"></span>
            @else
                <span class="logo">{{ $brand->initials() }}</span>
            @endif
            <span><span class="brand-name">{{ $brand->name }}</span><span class="brand-meta">Workspace brand</span></span>
        </div>
    </div>
    <div class="month-box">
        <div class="month-row">
            <a class="btn icon-only" aria-label="Bulan sebelumnya"
                href="{{ route('brands.workspace', ['brand' => $brand, 'year' => $previous->year, 'month' => $previous->month, 'type' => $type, 'view' => $view]) }}">
                <span class="icon"><svg><use href="#i-chevron-left"/></svg></span>
            </a>
            <div class="month-name">{{ $selectedMonth->locale('id')->translatedFormat('F Y') }}</div>
            <a class="btn icon-only" aria-label="Bulan berikutnya"
                href="{{ route('brands.workspace', ['brand' => $brand, 'year' => $next->year, 'month' => $next->month, 'type' => $type, 'view' => $view]) }}">
                <span class="icon"><svg><use href="#i-chevron-right"/></svg></span>
            </a>
        </div>
    </div>
    @include('workspace.partials.stats')
    @include('workspace.partials.mini-calendar')
</aside>
