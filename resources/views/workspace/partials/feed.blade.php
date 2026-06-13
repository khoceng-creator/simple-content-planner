<div class="feed-grid">
    @forelse ($plans as $plan)
        @include('workspace.partials.feed-item', ['plan' => $plan])
    @empty
        <div class="empty feed-empty">
            <strong>Tidak ada konten yang sesuai</strong>
            @if ($type !== 'semua' || $status !== 'semua')
                Coba ubah atau reset filter yang aktif.
            @else
                Tambah konten untuk bulan ini.
            @endif
        </div>
    @endforelse
</div>
