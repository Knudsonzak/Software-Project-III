@extends('layout')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Administration</p>
            <h1>Admin Dashboard</h1>
            <p class="page-description">Manage all employee accounts in one place.</p>
        </div>
        <a class="button button-primary" href="{{ url('/employees/create') }}"><span aria-hidden="true">+</span> Add employee</a>
    </div>

    <section class="card directory" aria-labelledby="directory-title">
        <div class="card-heading">
            <div>
                <h2 id="directory-title">Employee directory</h2>
                <p>All employees with options to view, edit, and delete accounts.</p>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Employee</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Role</th>
                        <th scope="col"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr>
                            <td>
                                <div class="employee-cell">
                                    <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($employee->name, 0, 1)) }}</span>
                                    <div><a class="employee-name" href="{{ url('/employees/' . $employee->emp_id) }}">{{ $employee->name }}</a><span class="secondary-text">{{ $employee->email }}</span></div>
                                </div>
                            </td>
                            <td>{{ $employee->phone ?: 'Not provided' }}</td>
                            <td><span class="badge">{{ ucfirst($employee->role) }}</span></td>
                            <td class="table-action">
                                <div class="action-buttons">
                                    <a href="{{ url('/employees/' . $employee->emp_id) }}">View</a>
                                    <a href="{{ url('/employees/' . $employee->emp_id . '/edit') }}">Edit</a>
                                    <form action="{{ url('/employees/' . $employee->emp_id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this employee?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-state"><strong>No employees yet</strong><span>Get started by adding your first employee account.</span><a class="button button-outline" href="{{ url('/employees/create') }}">Add employee</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection

