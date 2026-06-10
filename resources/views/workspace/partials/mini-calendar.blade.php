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
            <div class="calendar-day is-filled {{ $calendarDay['has_content'] ? 'has-content' : '' }} {{ $calendarDay['is_today'] ? 'is-today' : '' }}">
                {{ $calendarDay['day'] }}
            </div>
        @endforeach
    </div>
    <div class="calendar-alert">
        <span class="calendar-alert-label">Pengingat</span>
        <span>{{ $upcomingNotice }}</span>
    </div>
</div>
