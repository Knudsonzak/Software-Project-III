@extends('layout')

@section('title', 'Edit employee')

@section('content')
    <a class="back-link" href="{{ url('/employees/' . $employee->emp_id) }}"><span aria-hidden="true">&larr;</span> Employee details</a>
    <div class="page-heading">
        <div><p class="eyebrow">Employee profile</p><h1>Edit employee</h1><p class="page-description">Update the account details for {{ $employee->name }}.</p></div>
    </div>
    <form action="{{ url('/employees/' . $employee->emp_id) }}" method="POST">
        @csrf
        @method('PUT')
        @include('form', ['editing' => true])
    </form>
@endsection
