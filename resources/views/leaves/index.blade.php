@extends('layout')

@section('title', 'Request Leave')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Employee Portal</p>
            <h1>Submit Leave Request</h1>
            <p class="page-description">Submit a leave request and track its approval status.</p>
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

    <section class="card" aria-label="New leave request form">
        <form action="{{ url('/leaves') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="leave_type_id">Leave Type</label>
                <select id="leave_type_id" name="leave_type_id" required>
                    <option value="">Select a leave type</option>
                    @foreach ($leaveTypes as $type)
                        <option value="{{ $type->leave_type_id }}" {{ (string) old('leave_type_id') === (string) $type->leave_type_id ? 'selected' : '' }}>
                            {{ $type->name }} ({{ $type->default_days }} days)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required>
            </div>

            <div class="form-group">
                <label for="end_date">End Date</label>
                <input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" required>
            </div>

            <div class="form-group">
                <label for="reason">Reason (Optional)</label>
                <textarea id="reason" name="reason" rows="3">{{ old('reason') }}</textarea>
            </div>

            <button class="button button-primary" type="submit">Submit Leave Request</button>
        </form>
    </section>

    <section class="card" aria-label="My leave requests">
        <h2>My Leave Requests</h2>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Leave Type</th>
                        <th scope="col">Dates</th>
                        <th scope="col">Reason</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaveRequests as $leave)
                        <tr>
                            <td>{{ $leave->leaveType?->name ?? ('Type #' . $leave->leave_type_id) }}</td>
                            <td>
                                {{ optional($leave->start_date)->format('M j, Y') ?? $leave->start_date }}
                                &ndash;
                                {{ optional($leave->end_date)->format('M j, Y') ?? $leave->end_date }}
                            </td>
                            <td>{{ $leave->reason ?: 'No reason provided' }}</td>
                            <td><span class="badge">{{ ucfirst($leave->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">You have not submitted any leave requests yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection