<div class="stats">
    <a class="stat stat-filter {{ $type === 'semua' && $status === 'semua' ? 'is-active' : '' }}"
       href="{{ route('brands.workspace', ['brand' => $brand, 'year' => $year, 'month' => $month, 'type' => 'semua', 'status' => 'semua', 'view' => $view]) }}"
       aria-label="Tampilkan semua {{ $stats['total'] }} konten"
       @if ($type === 'semua' && $status === 'semua') aria-current="page" @endif>
        <div class="stat-num">{{ $stats['total'] }}</div><div class="stat-label">Total</div>
    </a>
    @foreach ($stats['types'] as $contentType)
        <a class="stat stat-filter {{ $type === $contentType['slug'] ? 'is-active' : '' }}"
           href="{{ route('brands.workspace', ['brand' => $brand, 'year' => $year, 'month' => $month, 'type' => $contentType['slug'], 'status' => $status, 'view' => $view]) }}"
           aria-label="Filter {{ $contentType['count'] }} konten tipe {{ $contentType['name'] }}"
           @if ($type === $contentType['slug']) aria-current="page" @endif>
            <div class="stat-num">{{ $contentType['count'] }}</div><div class="stat-label">{{ $contentType['name'] }}</div>
        </a>
    @endforeach
    <div class="work-board">
        <div class="work-board-grid">
            <a class="work-board-item stat-filter {{ $status === 'dibuat' ? 'is-active' : '' }}"
               href="{{ route('brands.workspace', ['brand' => $brand, 'year' => $year, 'month' => $month, 'type' => $type, 'status' => 'dibuat', 'view' => $view]) }}"
               aria-label="Filter {{ $stats['made'] }} konten yang sudah dibuat"
               @if ($status === 'dibuat') aria-current="page" @endif>
                <div class="work-board-num">{{ $stats['made'] }}</div><div class="work-board-label">Sudah dibuat</div>
            </a>
            <a class="work-board-item stat-filter {{ $status === 'belum' ? 'is-active' : '' }}"
               href="{{ route('brands.workspace', ['brand' => $brand, 'year' => $year, 'month' => $month, 'type' => $type, 'status' => 'belum', 'view' => $view]) }}"
               aria-label="Filter {{ $stats['remaining'] }} konten yang belum dibuat"
               @if ($status === 'belum') aria-current="page" @endif>
                <div class="work-board-num">{{ $stats['remaining'] }}</div><div class="work-board-label">Belum dibuat</div>
            </a>
        </div>
        @if ($type !== 'semua' || $status !== 'semua')
            <a class="work-board-reset" href="{{ route('brands.workspace', ['brand' => $brand, 'year' => $year, 'month' => $month, 'type' => 'semua', 'status' => 'semua', 'view' => $view]) }}">
                <span class="icon"><svg><use href="#i-close"/></svg></span>Reset filter
            </a>
        @else
            <div class="work-board-note">SEMANGAT KERJANYA GUYS !</div>
        @endif
    </div>
</div>
