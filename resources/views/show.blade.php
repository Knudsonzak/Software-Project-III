@extends('layout')

@section('title', 'Employee details')

@section('content')
    <a class="back-link" href="{{ url('/employees') }}"><span aria-hidden="true">&larr;</span> All employees</a>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Employee profile</p>
            <h1>Employee details</h1>
            <p class="page-description">View account and employment information.</p>
        </div>
        <a class="button button-primary" href="{{ url('/employees/' . $employee->emp_id . '/edit') }}">Edit employee</a>
    </div>

    <section class="card profile-card" aria-labelledby="employee-name">
        <div class="profile-header">
            <span class="avatar avatar-large" aria-hidden="true">{{ mb_strtoupper(mb_substr($employee->name, 0, 1)) }}</span>
            <div>
                <h2 id="employee-name">{{ $employee->name }}</h2>
                <p>{{ $employee->email }}</p>
                <span class="badge">{{ ucfirst($employee->role) }}</span>
            </div>
        </div>
        <div class="details-section">
            <div class="section-title"><h3>Account information</h3><p>Personal and contact details for this employee.</p></div>
            <dl class="details-grid">
                <div><dt>Employee ID</dt><dd>{{ $employee->emp_id }}</dd></div>
                <div><dt>Full name</dt><dd>{{ $employee->name }}</dd></div>
                <div><dt>Email address</dt><dd>{{ $employee->email }}</dd></div>
                <div><dt>Phone number</dt><dd>{{ $employee->phone ?: 'Not provided' }}</dd></div>
                <div><dt>Role</dt><dd>{{ ucfirst($employee->role) }}</dd></div>
            </dl>
        </div>
        <div class="details-section">
            <div class="section-title"><h3>Employment information</h3><p>Details associated with this employee's position.</p></div>
            <dl class="details-grid">
                <div><dt>Department ID</dt><dd>{{ $employee->department_id ?? 'Not assigned' }}</dd></div>
                <div><dt>Designation ID</dt><dd>{{ $employee->designation_id ?? 'Not assigned' }}</dd></div>
                <div><dt>Salary</dt><dd>{{ number_format((float) $employee->salary, 2) }}</dd></div>
            </dl>
        </div>
    </section>

    <section class="danger-zone" aria-labelledby="danger-title">
        <div><h2 id="danger-title">Delete employee</h2><p>Permanently remove this employee account. This action cannot be undone.</p></div>
        <form action="{{ url('/employees/' . $employee->emp_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this employee account? This action cannot be undone.')">
            @csrf
            @method('DELETE')
            <button class="button button-danger" type="submit">Delete employee</button>
        </form>
    </section>
@endsection
