@props([
    'type' => 'success',
    'message',
    'duration' => 5000,
])

<div
    class="flash {{ $type }}"
    role="{{ $type === 'error' ? 'alert' : 'status' }}"
    data-toast
    data-toast-duration="{{ $duration }}"
    style="--toast-duration: {{ $duration }}ms"
>
    <span class="flash-indicator" aria-hidden="true"></span>
    <p class="flash-message">{{ $message }}</p>
    <button class="flash-close" type="button" data-toast-close aria-label="Tutup notifikasi">
        <span class="icon" aria-hidden="true"><svg><use href="#i-close"/></svg></span>
    </button>
    <span class="flash-progress" aria-hidden="true"></span>
</div>
