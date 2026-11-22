@extends('layout')

@section('title', 'Request Leave')

@section('content')
    <div class="leave-page">
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

        <section class="card leave-form-card" aria-label="New leave request form">
            <div class="leave-section-heading">
                <div>
                    <h2>Request time off</h2>
                    <p>Choose your leave type and the dates you need off.</p>
                </div>
                <span class="leave-heading-icon" aria-hidden="true">✦</span>
            </div>

            <form class="leave-form" action="{{ route('leaves.store') }}" method="POST">
                @csrf

                <div class="leave-form-grid">
                    <div class="leave-field leave-field-wide">
                        <label for="leave_type_id">Leave type</label>
                        <select id="leave_type_id" name="leave_type_id" required>
                            <option value="">Select a leave type</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->leave_type_id }}" {{ (string) old('leave_type_id') === (string) $type->leave_type_id ? 'selected' : '' }}>
                                    {{ $type->name }} ({{ $type->default_days }} days)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="leave-field">
                        <label for="start_date">Start date</label>
                        <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required>
                    </div>

                    <div class="leave-field">
                        <label for="end_date">End date</label>
                        <input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" required>
                    </div>

                    <div class="leave-field leave-field-wide">
                        <label for="reason">Reason <span>(optional)</span></label>
                        <textarea id="reason" name="reason" rows="4" maxlength="2000" placeholder="Add any details that will help your manager review the request.">{{ old('reason') }}</textarea>
                    </div>
                </div>

                <div class="leave-form-footer">
                    <p>Requests are sent to your administrator for approval.</p>
                    <button class="button button-primary" type="submit">Submit request <span aria-hidden="true">&rarr;</span></button>
                </div>
            </form>
        </section>

        <section class="card leave-history-card" aria-label="My leave requests">
            <div class="leave-section-heading">
                <div>
                    <h2>My leave requests</h2>
                    <p>Check the dates and approval status of your requests.</p>
                </div>
                <span class="leave-request-count">{{ $leaveRequests->count() }} {{ \Illuminate\Support\Str::plural('request', $leaveRequests->count()) }}</span>
            </div>
            <div class="table-wrap">
                <table class="leave-table">
                    <thead>
                        <tr>
                            <th scope="col">Leave type</th>
                            <th scope="col">Dates</th>
                            <th scope="col">Reason</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaveRequests as $leave)
                            <tr>
                                <td class="leave-type-cell">{{ $leave->leaveType?->name ?? ('Type #' . $leave->leave_type_id) }}</td>
                                <td>
                                    {{ optional($leave->start_date)->format('M j, Y') ?? $leave->start_date }}
                                    &ndash;
                                    {{ optional($leave->end_date)->format('M j, Y') ?? $leave->end_date }}
                                </td>
                                <td>{{ $leave->reason ?: 'No reason provided' }}</td>
                                <td><span class="leave-status leave-status-{{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="leave-empty-state">
                                    <span class="leave-empty-icon" aria-hidden="true">◷</span>
                                    <strong>No requests yet</strong>
                                    <span>Once you submit a request, you can track its status here.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection