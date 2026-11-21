<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Dashboard | SmartHR</title>
    @vite('resources/css/app.css')
</head>
<body class="employee-portal">
    <div class="employee-shell">
        <aside class="employee-sidebar">
            <a class="employee-brand" href="{{ route('dashboard') }}"><span class="employee-brand-mark" aria-hidden="true">✦</span> SmartHR</a>
            <div class="employee-identity">
                <span class="employee-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <span><small>Employee workspace</small><strong>{{ auth()->user()->name }}</strong></span>
            </div>
            <nav class="employee-nav" aria-label="Employee navigation">
                <a class="employee-nav-item is-active" href="{{ route('dashboard') }}" aria-current="page"><span aria-hidden="true">▦</span> Dashboard</a>
                <span class="employee-nav-item is-disabled"><span aria-hidden="true">◷</span> Timesheets</span>
                <span class="employee-nav-item is-disabled"><span aria-hidden="true">◫</span> Leave requests</span>
                <span class="employee-nav-item is-disabled"><span aria-hidden="true">▤</span> Payroll</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="employee-nav-item employee-logout" type="submit"><span aria-hidden="true">↪</span> Log out</button>
                </form>
            </nav>
            <div class="employee-sidebar-footer">Your workspace, at a glance.</div>
        </aside>

        <main class="employee-main">
            <header class="employee-header">
                <div>
                    <h1>Hey, {{ auth()->user()->name }}!</h1>
                    <p>Your work overview &middot; {{ now()->format('F j, Y') }}</p>
                </div>
                <button class="employee-clock-button" type="button" disabled aria-describedby="clock-unavailable">{{ $activeLog ? 'Clock out' : 'Clock in' }}</button>
            </header>

            @if (session('success'))
                <div class="notice notice-success" role="status">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="notice notice-error" role="alert">{{ session('error') }}</div>
            @endif

            <div class="employee-grid">
                <section class="employee-card employee-attendance" aria-labelledby="hours-title">
                    <div class="employee-card-heading">
                        <div>
                            <h2 id="hours-title">Hours worked</h2>
                            <p>Last 7 days</p>
                        </div>
                        <span class="employee-legend"><span></span> Hours worked</span>
                    </div>
                    <div class="employee-total">{{ number_format($monthHours, 1) }} <span>hrs</span></div>
                    <p class="employee-subtle">Worked this month</p>
                    @php($maxHours = max(1, $weekly->max('hours')))
                    <div class="employee-bar-chart" aria-label="Hours worked over the last seven days">
                        @foreach ($weekly as $day)
                            <div class="employee-bar-column">
                                <span class="employee-bar-value">{{ number_format($day['hours'], 1) }}</span>
                                <div class="employee-bar-track">
                                    <div class="employee-bar" style="height: {{ $day['hours'] / $maxHours * 100 }}%" title="{{ $day['date'] }}: {{ number_format($day['hours'], 1) }} hours"></div>
                                </div>
                                <span class="employee-bar-label">{{ $day['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="employee-footnote">Hours are shown on the day you clocked in, less recorded breaks.</p>
                </section>

                <section class="employee-card employee-tracker" aria-labelledby="tracker-title">
                    <div class="employee-card-heading">
                        <h2 id="tracker-title">Time tracker</h2>
                        <span class="employee-status {{ $activeLog ? 'is-clocked-in' : '' }}">{{ $activeLog ? 'Clocked in' : 'Off the clock' }}</span>
                    </div>
                    <div class="employee-gauge" aria-label="{{ $activeLog ? 'Current session: ' . number_format($activeHours, 1) . ' hours' : 'Not clocked in' }}">
                        <div class="employee-gauge-center">
                            <strong>{{ number_format($activeHours, 1) }}</strong>
                            <span>session hours</span>
                        </div>
                    </div>
                    <div class="employee-tracker-details">
                        <span>Current session</span>
                        <strong>{{ $activeLog ? 'Since ' . \Carbon\Carbon::parse($activeLog->clock_in)->format('M j, g:i A') : 'Not clocked in' }}</strong>
                    </div>
                    <p class="employee-subtle" id="clock-unavailable">Clock in and out is not available yet.</p>
                </section>

                <section class="employee-card employee-leaves" aria-labelledby="leave-title">
                    <div class="employee-card-heading">
                        <div>
                            <h2 id="leave-title">My leave requests</h2>
                            <p>Recent requests</p>
                        </div>
                        <span class="employee-count">{{ $pendingLeaves }} pending</span>
                    </div>
                    <div class="employee-table-wrap">
                        <table class="employee-table">
                            <thead><tr><th scope="col">Leave type</th><th scope="col">Dates</th><th scope="col">Status</th></tr></thead>
                            <tbody>
                                @forelse ($leaves as $leave)
                                    <tr>
                                        <td>{{ $leave->leave_type }}</td>
                                        <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('M j, Y') }} &ndash; {{ \Carbon\Carbon::parse($leave->end_date)->format('M j, Y') }}</td>
                                        <td><span class="employee-status employee-status-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="employee-empty">No leave requests yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="employee-card employee-payroll" aria-labelledby="payroll-title">
                    <div class="employee-card-heading">
                        <div>
                            <h2 id="payroll-title">Recent payslips</h2>
                            <p>Your latest payroll records</p>
                        </div>
                    </div>
                    @forelse ($payroll as $pay)
                        <div class="employee-pay-row">
                            <div>
                                <strong>${{ number_format($pay->net_salary, 2) }}</strong>
                                <span>{{ \Carbon\Carbon::parse($pay->created_at)->format('M j, Y') }}</span>
                            </div>
                            <span class="employee-status employee-status-{{ $pay->payment_status }}">{{ ucfirst($pay->payment_status) }}</span>
                        </div>
                    @empty
                        <p class="employee-empty">No payroll records yet.</p>
                    @endforelse
                </section>
            </div>
        </main>
    </div>
</body>
</html>
