@extends('layout')

@section('title', 'Manage Leave Requests')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Administration</p>
            <h1>Employee Leave Requests</h1>
            <p class="page-description">Review, approve, or reject submitted employee leave requests.</p>
        </div>
        <div class="filter-links">
            <a class="button {{ empty($statusFilter) ? 'button-primary' : 'button-outline' }}" href="{{ url('/admin/leaves') }}">All</a>
            <a class="button {{ ($statusFilter ?? '') === 'pending' ? 'button-primary' : 'button-outline' }}" href="{{ url('/admin/leaves?status=pending') }}">Pending</a>
            <a class="button {{ ($statusFilter ?? '') === 'approved' ? 'button-primary' : 'button-outline' }}" href="{{ url('/admin/leaves?status=approved') }}">Approved</a>
            <a class="button {{ ($statusFilter ?? '') === 'rejected' ? 'button-primary' : 'button-outline' }}" href="{{ url('/admin/leaves?status=rejected') }}">Rejected</a>
        </div>
    </div>

    <section class="card" aria-label="Leave requests list">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Employee</th>
                        <th scope="col">Leave Type</th>
                        <th scope="col">Dates</th>
                        <th scope="col">Reason</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leaveRequests as $leave)
                        <tr>
                            <td>{{ $leave->employee?->name ?? ('Employee #' . $leave->emp_id) }}</td>
                            <td>{{ $leave->leaveType?->name ?? ('Type #' . $leave->leave_type_id) }}</td>
                            <td>
                                {{ optional($leave->start_date)->format('M j, Y') ?? $leave->start_date }}
                                &ndash;
                                {{ optional($leave->end_date)->format('M j, Y') ?? $leave->end_date }}
                            </td>
                            <td>{{ $leave->reason ?: 'No reason provided' }}</td>
                            <td><span class="badge">{{ ucfirst($leave->status) }}</span></td>
                            <td>
                                <div class="action-buttons">
                                    @if ($leave->status !== 'approved')
                                        <form action="{{ url('/admin/leaves/' . $leave->form_id . '/approve') }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button class="button button-primary" type="submit">Approve</button>
                                        </form>
                                    @endif

                                    @if ($leave->status !== 'rejected')
                                        <form action="{{ url('/admin/leaves/' . $leave->form_id . '/reject') }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('PATCH')
                                            <button class="button button-danger" type="submit">Reject</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">No leave requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection