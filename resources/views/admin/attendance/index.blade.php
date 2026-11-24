@extends('layout')

@section('title', 'Attendance Management')

@section('content')
    <div class="page-heading attendance-page-heading">
        <div>
            <p class="eyebrow">Administration</p>
            <h1>Attendance Management</h1>
            <p class="page-description">Review employee clock-ins, clock-outs, and hours worked.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="notice notice-error" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="panel attendance-filter-panel" aria-labelledby="attendance-filters-title">
        <div class="attendance-section-heading">
            <div>
                <h2 id="attendance-filters-title">Filter attendance</h2>
                <p>Select a date range and optionally narrow results to one employee.</p>
            </div>
        </div>
        <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.attendance.index') }}">
            <div class="col-sm-6 col-xl-3">
                <label class="form-label" for="start_date">Start date</label>
                <input class="form-control" id="start_date" name="start_date" type="date" value="{{ $startDate }}" required>
            </div>
            <div class="col-sm-6 col-xl-3">
                <label class="form-label" for="end_date">End date</label>
                <input class="form-control" id="end_date" name="end_date" type="date" value="{{ $endDate }}" required>
            </div>
            <div class="col-sm-8 col-xl-4">
                <label class="form-label" for="employee_id">Employee</label>
                <select class="form-select" id="employee_id" name="employee_id">
                    <option value="">All employees</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->emp_id }}" @selected((string) $selectedEmployee === (string) $employee->emp_id)>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-xl-2 d-flex gap-2">
                <button class="button button-primary attendance-filter-button" type="submit">Apply filters</button>
                <a class="button button-outline" href="{{ route('admin.attendance.index') }}">Reset</a>
            </div>
        </form>
    </section>

    <div class="row g-3 attendance-summary">
        <div class="col-sm-6 col-xl-4">
            <div class="stat attendance-summary-card">
                <div class="hint">Matching time entries</div>
                <div class="num">{{ number_format($attendanceLogs->total()) }}</div>
                <div class="hint">{{ $startDate === $endDate ? \Carbon\Carbon::parse($startDate)->format('M j, Y') : \Carbon\Carbon::parse($startDate)->format('M j') . ' – ' . \Carbon\Carbon::parse($endDate)->format('M j, Y') }}</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat attendance-summary-card">
                <div class="hint">Total hours worked</div>
                <div class="num">{{ number_format($totalHours, 2) }}</div>
                <div class="hint">Includes current open shifts</div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="stat attendance-summary-card">
                <div class="hint">Employees in results</div>
                <div class="num">{{ number_format($totalEmployees) }}</div>
                <div class="hint">Across all matching records</div>
            </div>
        </div>
    </div>

    <section class="panel attendance-table-panel" aria-labelledby="attendance-table-title">
        <div class="attendance-section-heading attendance-table-heading">
            <div>
                <h2 id="attendance-table-title">Employee time records</h2>
                <p>Showing {{ $attendanceLogs->firstItem() ?? 0 }}–{{ $attendanceLogs->lastItem() ?? 0 }} of {{ $attendanceLogs->total() }} records.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table attendance-table align-middle">
                <thead>
                    <tr>
                        <th scope="col">Employee</th>
                        <th scope="col">Date</th>
                        <th scope="col">Clock in</th>
                        <th scope="col">Clock out</th>
                        <th scope="col">Break</th>
                        <th scope="col">Hours</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendanceLogs as $log)
                        <tr>
                            <td>
                                <span class="attendance-employee-name">{{ $log->employee_name }}</span>
                                <span class="attendance-employee-email">{{ $log->employee_email }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($log->clock_in)->format('M j, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->clock_in)->format('g:i A') }}</td>
                            <td>{{ $log->clock_out ? \Carbon\Carbon::parse($log->clock_out)->format('g:i A') : '—' }}</td>
                            <td>{{ (int) $log->break_minutes }} min</td>
                            <td class="attendance-hours">{{ number_format($log->hours_worked, 2) }}</td>
                            <td>
                                @if (! $log->clock_out)
                                    <span class="attendance-status attendance-status-active">In progress</span>
                                @else
                                    <span class="attendance-status attendance-status-{{ $log->status }}">{{ ucfirst($log->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="attendance-empty-state">
                                No attendance records match these filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($attendanceLogs->hasPages())
            <div class="attendance-pagination">
                {{ $attendanceLogs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
@endsection
