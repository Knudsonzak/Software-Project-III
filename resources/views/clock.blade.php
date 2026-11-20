@extends('layout')

@section('title', 'Clock In / Out')

@section('content')
    <div class="page-heading clock-heading">
        <div>
            <p class="eyebrow">Time &amp; Attendance</p>
            <h1>Clock In / Out</h1>
            <p class="page-description">{{ $employee->name }} &middot; {{ now()->format('l, F j') }}</p>
        </div>
        <div class="live-clock" aria-live="polite">
            <span class="live-clock-time" id="live-clock-time">{{ now()->format('h:i:s A') }}</span>
            <span class="live-clock-zone">{{ now()->tzName }}</span>
        </div>
    </div>

    <div class="clock-grid">
        <section class="card clock-action-card" aria-labelledby="clock-status-title">
            <div class="clock-status-icon {{ $openLog ? 'is-active' : '' }}" aria-hidden="true">
                <span></span>
            </div>
            <div class="clock-status-copy">
                <p class="clock-status-label">Current status</p>
                <h2 id="clock-status-title">{{ $openLog ? 'Clocked in' : 'Clocked out' }}</h2>
                @if ($openLog)
                    <p>Started at <strong>{{ $openLog->clock_in->format('g:i A') }}</strong></p>
                @else
                    <p>You are ready to start your shift.</p>
                @endif
            </div>

            @if ($openLog)
                <div class="elapsed-time">
                    <span>Time elapsed</span>
                    <strong id="elapsed-time" data-clock-in="{{ $openLog->clock_in->toIso8601String() }}">00:00:00</strong>
                </div>
                <form method="POST" action="{{ route('clock-out') }}">
                    @csrf
                    <button class="clock-button clock-button-out" type="submit">Clock Out</button>
                </form>
            @else
                <form method="POST" action="{{ route('clock-in') }}">
                    @csrf
                    <button class="clock-button clock-button-in" type="submit">Clock In</button>
                </form>
            @endif
        </section>

        <div class="clock-summary">
            <section class="card summary-card">
                <p>Hours today</p>
                <strong>{{ number_format($todayHours, 2) }}<span> hrs</span></strong>
                <div class="summary-rule"></div>
                <small>{{ $todayLogs->count() }} {{ Str::plural('entry', $todayLogs->count()) }} today</small>
            </section>
            <section class="card summary-card">
                <p>This week</p>
                <strong>{{ number_format($weeklyHours, 2) }}<span> hrs</span></strong>
                <div class="summary-rule"></div>
                <small>Monday through Sunday</small>
            </section>
        </div>
    </div>

    <section class="card recent-card" aria-labelledby="recent-title">
        <div class="card-heading">
            <div>
                <h2 id="recent-title">Recent time entries</h2>
                <p>Your latest clock-in and clock-out activity.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Clock in</th>
                        <th scope="col">Clock out</th>
                        <th scope="col">Hours</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentLogs as $log)
                        <tr>
                            <td class="entry-date">{{ $log->clock_in->format('M j, Y') }}</td>
                            <td>{{ $log->clock_in->format('g:i A') }}</td>
                            <td>{{ $log->clock_out ? $log->clock_out->format('g:i A') : '—' }}</td>
                            <td>{{ $log->hours !== null ? number_format((float) $log->hours, 2) : '—' }}</td>
                            <td>
                                @if (! $log->clock_out)
                                    <span class="status-pill status-active">In progress</span>
                                @else
                                    <span class="status-pill status-{{ $log->status }}">{{ ucfirst($log->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <strong>No time entries yet</strong>
                                <span>Clock in to create your first time entry.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <script>
        const updateLiveClock = () => {
            const now = new Date();
            const time = document.getElementById('live-clock-time');
            if (time) {
                time.textContent = now.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }
        };

        const elapsed = document.getElementById('elapsed-time');
        if (elapsed) {
            const startedAt = new Date(elapsed.dataset.clockIn);
            const updateElapsed = () => {
                const seconds = Math.max(0, Math.floor((Date.now() - startedAt.getTime()) / 1000));
                const hours = String(Math.floor(seconds / 3600)).padStart(2, '0');
                const minutes = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
                const secs = String(seconds % 60).padStart(2, '0');
                elapsed.textContent = `${hours}:${minutes}:${secs}`;
            };
            updateElapsed();
            setInterval(updateElapsed, 1000);
        }

        updateLiveClock();
        setInterval(updateLiveClock, 1000);
    </script>
@endsection
