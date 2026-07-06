@extends('layouts.app')

@section('title', 'Edit Pegawai')

@section('content')
<div class="mb-4">
    <h1 class="h3 page-title mb-1">Edit Pegawai</h1>
    <div class="text-secondary">{{ $employee->attendanceLabel() }}</div>
</div>

<div class="stat-card p-4">
    <form method="POST" action="{{ route('employees.update', $employee) }}">
        @csrf
        @method('PUT')
        @include('employees._form')
    </form>
</div>
@endsection
