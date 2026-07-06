@extends('layouts.app')

@section('content')
    <form class="toolbar" method="GET" action="{{ route('explore') }}">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari…" autocomplete="off">
        <select name="sort" aria-label="Urutkan" onchange="this.form.submit()">
            <option value="trending" @selected($sort === 'trending')>Trending</option>
            <option value="newest" @selected($sort === 'newest')>Terbaru</option>
        </select>
        <button type="submit" class="btn">Cari</button>
    </form>

    <main class="gallery">
        @forelse ($splats as $splat)
            <a class="card" href="{{ route('splat.show', $splat) }}">
                <div class="thumb">
                    @if ($splat->thumbnailUrl())
                        <img src="{{ $splat->thumbnailUrl() }}" alt="{{ $splat->title }}" loading="lazy">
                    @else
                        <div class="placeholder">{{ strtoupper(substr($splat->title, 0, 1)) }}</div>
                    @endif
                </div>
                <div class="info">
                    <h2>{{ $splat->title }}</h2>
                    <div class="author">{{ $splat->displayAuthor() }}</div>
                    @if ($splat->description)
                        <div class="desc">{{ $splat->description }}</div>
                    @endif
                    <div class="stats">
                        <span><svg class="i" viewBox="0 0 24 24"><use href="#i-eye"/></svg> {{ number_format($splat->views) }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="status">
                @if ($q)
                    Tidak ada splat yang cocok dengan "{{ $q }}".
                @else
                    Belum ada splat. <a href="{{ route('manage.create') }}">Unggah yang pertama!</a>
                @endif
            </div>
        @endforelse
    </main>

    @if ($splats->hasPages())
        <nav class="pagination">
            @if ($splats->onFirstPage())
                <span class="btn disabled">&larr; Sebelumnya</span>
            @else
                <a class="btn" href="{{ $splats->previousPageUrl() }}">&larr; Sebelumnya</a>
            @endif
            <span class="page-info">Hal. {{ $splats->currentPage() }} / {{ $splats->lastPage() }}</span>
            @if ($splats->hasMorePages())
                <a class="btn" href="{{ $splats->nextPageUrl() }}">Berikutnya &rarr;</a>
            @else
                <span class="btn disabled">Berikutnya &rarr;</span>
            @endif
        </nav>
    @endif
@endsection
