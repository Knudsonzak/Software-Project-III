@extends('layout')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="page-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="small-muted">ADMIN OVERVIEW</div>
            <h1 class="fw-bold mb-1">Workforce Dashboard</h1>
            <div class="small-muted">Live database overview &middot; {{ now()->format('F j, Y') }}</div>
        </div>
        <div class="admin-dashboard-actions">
            <a class="button button-outline" href="{{ route('leaves.index') }}">Request leave</a>
            <a class="button button-primary" href="{{ route('admin.leaves.index') }}">Review leave requests</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat">
                <div class="hint">Total employees</div>
                <div class="num">{{ $employeeCount }}</div>
                <div class="hint">Employee accounts</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat">
                <div class="hint">Present today</div>
                <div class="num">{{ $present }}</div>
                <div class="hint">{{ $absent }} not clocked in today</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat">
                <div class="hint">Pending leave</div>
                <div class="num">{{ $pendingLeaves }}</div>
                <div class="hint">Requests to review</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat">
                <div class="hint">Net payroll this month</div>
                <div class="num">${{ number_format($monthPayroll, 2) }}</div>
                <div class="hint">Payroll records created this month</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <section class="panel">
                <h2>Attendance trend &middot; last 7 days</h2>
                <div class="chart-wrap"><canvas id="attendanceChart" aria-label="Attendance trend over the last seven days" role="img"></canvas></div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="panel">
                <h2>Employees by department</h2>
                <div class="chart-wrap"><canvas id="departmentChart" aria-label="Employee distribution by department" role="img"></canvas></div>
            </section>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <section class="panel">
                <h2>Net payroll entries &middot; last 6 months</h2>
                <div class="chart-wrap"><canvas id="payrollChart" aria-label="Net payroll over the last six months" role="img"></canvas></div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="panel">
                <h2>Recent leave requests</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th scope="col">Employee</th><th scope="col">Dates</th><th scope="col">Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($recentLeaves as $leave)
                                <tr>
                                    <td>{{ $leave->name }}</td>
                                    <td>{{ $leave->start_date }} &ndash; {{ $leave->end_date }}</td>
                                    <td><span class="pill">{{ ucfirst($leave->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="small-muted">No leave requests yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const attendance = @json($attendanceTrend);
        const departments = @json($departments);
        const payroll = @json($payrollTrend);
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        };

        new Chart(document.getElementById('attendanceChart'), {
            type: 'line',
            data: {
                labels: attendance.map(item => item.label),
                datasets: [{
                    data: attendance.map(item => item.value),
                    borderColor: '#655cf5',
                    backgroundColor: 'rgba(101, 92, 245, .12)',
                    fill: true,
                    tension: .35
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('departmentChart'), {
            type: 'doughnut',
            data: {
                labels: departments.map(item => item.label),
                datasets: [{
                    data: departments.map(item => item.value),
                    backgroundColor: ['#655cf5', '#16a6a0', '#f5b94d', '#ee789a', '#7f96bd', '#8c70c9']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        new Chart(document.getElementById('payrollChart'), {
            type: 'bar',
            data: {
                labels: payroll.map(item => item.label),
                datasets: [{
                    data: payroll.map(item => item.value),
                    backgroundColor: '#16a6a0',
                    borderRadius: 7
                }]
            },
            options: chartOptions
        });
    </script>
@endsection
