@extends('layout')

@section('title', 'Add employee')

@section('content')
    <a class="back-link" href="{{ url('/employees') }}"><span aria-hidden="true">&larr;</span> All employees</a>
    <div class="page-heading">
        <div><p class="eyebrow">People management</p><h1>Add employee</h1><p class="page-description">Create an account for a new member of your team.</p></div>
    </div>
    <form action="{{ url('/employees') }}" method="POST">
        @csrf
        @include('form', ['editing' => false])
    </form>
@endsection
