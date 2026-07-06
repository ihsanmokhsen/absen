@extends('layouts.app')

@section('title', 'Tambah Pegawai')

@section('content')
<div class="mb-4">
    <h1 class="h3 page-title mb-1">Tambah Pegawai</h1>
    <div class="text-secondary">Masukkan data pegawai baru.</div>
</div>

<div class="stat-card p-4">
    <form method="POST" action="{{ route('employees.store') }}">
        @csrf
        @include('employees._form')
    </form>
</div>
@endsection
