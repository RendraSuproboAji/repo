@extends('layouts.app')

@section('title', 'Login — '.config('app.name'))

@section('content')
    <main class="page-narrow">
        <div class="panel">
            <h1>Login</h1>

            <form method="POST" action="{{ route('login') }}" class="form">
                @csrf
                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <div class="form-field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    @error('password')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <label class="check">
                    <input type="checkbox" name="remember" value="1"> Ingat saya
                </label>
                <button type="submit" class="btn btn-accent btn-block">Login</button>
            </form>

            <p class="muted">Belum punya akun? <a href="{{ route('register') }}">Sign Up</a></p>
        </div>
    </main>
@endsection
