@extends('layouts.app')

@section('title', 'Login Admin')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 78vh;">
    <div class="col-md-6 col-lg-4">
        <div class="text-center mb-4">
            <h1 class="h4 page-title mb-1">Absensi Apel Pagi</h1>
            <div class="text-secondary">BPAD Provinsi NTT</div>
        </div>
        <div class="stat-card p-4 shadow-sm">
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="login">Akun / Email</label>
                    <input id="login" type="text" name="login" value="{{ old('login') }}" class="form-control @error('login') is-invalid @enderror" required autofocus>
                    @error('login')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                    <label class="form-check-label" for="remember">Ingat sesi login</label>
                </div>
                <button class="btn btn-primary w-100" type="submit">Masuk</button>
            </form>
        </div>
        <div class="text-center mt-3">
            <a class="btn btn-outline-primary" href="{{ route('guide') }}">Panduan Penggunaan</a>
        </div>
    </div>
</div>
@endsection
