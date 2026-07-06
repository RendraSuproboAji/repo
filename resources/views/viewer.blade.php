<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <title>{{ $splat->title }} — {{ config('app.name') }}</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>✨</text></svg>">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="viewer-page">
    @include('partials.icons')

    <canvas id="viewer-canvas"></canvas>

    <div class="viewer-topbar">
        <a class="btn" href="{{ route('explore') }}">&larr; Galeri</a>
        <span class="scene-title">{{ $splat->title }}</span>
        <span class="scene-meta">
            {{ $splat->displayAuthor() }} &middot;
            <svg class="i" viewBox="0 0 24 24"><use href="#i-eye"/></svg> {{ number_format($splat->views) }}
        </span>
        <span class="spacer"></span>
        @if (auth()->id() === $splat->user_id)
            <a class="btn" href="{{ route('manage.edit', $splat) }}">Edit</a>
        @endif
        <button class="btn" id="btn-fullscreen" title="Layar penuh">⛶</button>
        <button class="btn" data-menu-open aria-label="Buka menu">☰</button>
    </div>

    <div class="viewer-hint" id="viewer-hint">
        seret: putar &middot; klik kanan / dua jari: geser &middot; scroll / cubit: zoom
    </div>

    <div class="overlay" id="overlay">
        <div class="spinner" id="spinner"></div>
        <div class="progress" id="progress">Memuat scene…</div>
    </div>

    <script>
        window.sceneConfig = @json($splat->viewerConfig());
    </script>
    <script type="module" src="{{ asset('js/viewer.js') }}"></script>
    @include('partials.drawer')
    <script type="module" src="{{ asset('js/menu.js') }}"></script>
</body>
</html>
