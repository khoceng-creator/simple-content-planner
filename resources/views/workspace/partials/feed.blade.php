<div class="feed-grid">
    @forelse ($plans as $plan)
        @include('workspace.partials.feed-item', ['plan' => $plan])
    @empty
        <div class="empty feed-empty"><strong>Belum ada konten</strong>Tambah konten untuk bulan ini.</div>
    @endforelse
</div>
