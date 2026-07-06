@extends('layouts.app')

@section('title', 'Sign Up — '.config('app.name'))

@section('content')
    <main class="page-narrow">
        <div class="panel">
            <h1>Sign Up</h1>

            <form method="POST" action="{{ route('register') }}" class="form">
                @csrf
                <div class="form-field">
                    <label for="name">Nama</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <div class="form-field">
                    <label for="password">Password (min. 8 karakter)</label>
                    <input type="password" id="password" name="password" required>
                    @error('password')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <div class="form-field">
                    <label for="password_confirmation">Ulangi password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
                <button type="submit" class="btn btn-accent btn-block">Buat Akun</button>
            </form>

            <p class="muted">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
        </div>
    </main>
@endsection
