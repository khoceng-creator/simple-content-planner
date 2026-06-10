<div class="timeline">
    @forelse ($plans as $plan)
        @include('workspace.partials.timeline-item', ['plan' => $plan])
    @empty
        <div class="empty"><strong>Belum ada konten</strong>Tambah konten untuk bulan ini.</div>
    @endforelse
</div>
