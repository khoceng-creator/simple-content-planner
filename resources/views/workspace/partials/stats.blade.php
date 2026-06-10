<div class="stats">
    <div class="stat"><div class="stat-num">{{ $stats['total'] }}</div><div class="stat-label">Total</div></div>
    @foreach ($stats['types'] as $contentType)
        <div class="stat"><div class="stat-num">{{ $contentType['count'] }}</div><div class="stat-label">{{ $contentType['name'] }}</div></div>
    @endforeach
    <div class="work-board">
        <div class="work-board-grid">
            <div class="work-board-item"><div class="work-board-num">{{ $stats['made'] }}</div><div class="work-board-label">Sudah dibuat</div></div>
            <div class="work-board-item"><div class="work-board-num">{{ $stats['remaining'] }}</div><div class="work-board-label">Belum dibuat</div></div>
        </div>
        <div class="work-board-note">SEMANGAT KERJANYA GUYS.!</div>
    </div>
</div>
