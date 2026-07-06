<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✨</text></svg>">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    @include('partials.icons')

    <header class="topbar">
        <a class="brand" href="{{ route('explore') }}">
            <span class="brand-logo" aria-hidden="true"></span>
            <span>Splat <span class="accent">Gallery</span></span>
        </a>
        <span class="spacer"></span>
        @auth
            <a class="btn btn-accent topbar-upload" href="{{ route('manage.create') }}">
                <svg class="i" viewBox="0 0 24 24"><use href="#i-upload"/></svg> Upload Splat
            </a>
        @endauth
        <button class="icon-btn" data-menu-open aria-label="Buka menu">
            <svg class="i" viewBox="0 0 24 24"><use href="#i-panel"/></svg>
        </button>
    </header>

    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    @yield('content')

    <footer class="site-footer">
        Dibuat dengan <a href="https://superspl.at/editor" target="_blank" rel="noopener">SuperSplat</a>
        &middot; ditenagai <a href="https://playcanvas.com" target="_blank" rel="noopener">PlayCanvas</a>
        &middot; <a href="https://laravel.com" target="_blank" rel="noopener">Laravel</a>
    </footer>

    @include('partials.drawer')
    <script type="module" src="{{ asset('js/menu.js') }}"></script>
</body>
</html>
