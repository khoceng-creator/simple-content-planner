<div class="timeline">
    @forelse ($plans as $plan)
        @include('workspace.partials.timeline-item', ['plan' => $plan])
    @empty
        <div class="empty">
            <strong>Tidak ada konten yang sesuai</strong>
            @if ($type !== 'semua' || $status !== 'semua')
                Coba ubah atau reset filter yang aktif.
            @else
                Tambah konten untuk bulan ini.
            @endif
        </div>
    @endforelse
</div>
