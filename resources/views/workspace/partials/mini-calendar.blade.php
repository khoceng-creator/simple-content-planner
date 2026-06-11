@php($firstDay = $selectedMonth->dayOfWeek)
<div class="calendar-board">
    <div class="calendar-head">
        <span>Kalender {{ $selectedMonth->locale('id')->translatedFormat('F Y') }}</span>
        <span class="calendar-today-label">Hari ini {{ now('Asia/Jakarta')->format('d/m') }}</span>
    </div>
    <div class="calendar-grid">
        @foreach (['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $day)
            <div class="calendar-day-name">{{ $day }}</div>
        @endforeach
        @for ($blank = 0; $blank < $firstDay; $blank++)
            <div class="calendar-day"></div>
        @endfor
        @foreach ($calendarDays as $calendarDay)
            @if ($calendarDay['plans']->isNotEmpty())
                <button
                    class="calendar-day is-filled has-content {{ $calendarDay['is_today'] ? 'is-today' : '' }}"
                    type="button"
                    data-calendar-day
                    data-calendar-target="calendar-schedule-{{ $calendarDay['day'] }}"
                    aria-controls="calendar-schedule-{{ $calendarDay['day'] }}"
                    aria-expanded="false"
                    aria-label="{{ $calendarDay['day'] }} {{ $selectedMonth->locale('id')->translatedFormat('F Y') }}, {{ $calendarDay['plans']->count() }} jadwal"
                >
                    {{ $calendarDay['day'] }}
                </button>
            @else
                <div class="calendar-day is-filled {{ $calendarDay['is_today'] ? 'is-today' : '' }}">
                    {{ $calendarDay['day'] }}
                </div>
            @endif
        @endforeach
    </div>

    <div class="calendar-schedules" data-calendar-schedules>
        @foreach ($calendarDays->filter(fn ($day) => $day['plans']->isNotEmpty()) as $calendarDay)
            <section class="calendar-schedule-panel" id="calendar-schedule-{{ $calendarDay['day'] }}" data-calendar-panel hidden>
                <div class="calendar-schedule-head">
                    <strong>{{ $calendarDay['day'] }} {{ $selectedMonth->locale('id')->translatedFormat('F Y') }}</strong>
                    <span>{{ $calendarDay['plans']->count() }} jadwal</span>
                </div>
                <div class="calendar-schedule-list">
                    @foreach ($calendarDay['plans'] as $plan)
                        <a class="calendar-schedule-item" href="{{ route('contents.preview', $plan) }}">
                            <span>
                                <strong>{{ $plan->headline }}</strong>
                                <small>{{ $contentTypeLabels[$plan->type] ?? $plan->type_label }}</small>
                            </span>
                            <time>{{ $plan->posting_time ? substr($plan->posting_time, 0, 5) : '--:--' }}</time>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <div class="calendar-alert">
        <span class="calendar-alert-label">5 Jadwal Terdekat</span>
        @forelse ($upcomingNotices as $notice)
            <a class="calendar-reminder-item" href="{{ route('contents.preview', $notice['plan']) }}">
                <span>{{ $notice['message'] }}</span>
                <small>{{ $notice['plan']->headline }}</small>
            </a>
        @empty
            <span class="calendar-reminder-empty">Belum ada jadwal konten mendatang.</span>
        @endforelse
    </div>
</div>
