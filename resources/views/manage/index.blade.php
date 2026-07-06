@extends('layouts.app')

@section('title', 'Your Splats — '.config('app.name'))

@section('content')
    <main class="page-wide">
        <div class="page-head">
            <h1>Your Splats</h1>
            <a class="btn btn-accent" href="{{ route('manage.create') }}">
                <svg class="i" viewBox="0 0 24 24"><use href="#i-upload"/></svg> Upload Splat
            </a>
        </div>

        @if ($splats->isEmpty())
            <div class="empty-state">
                <div class="empty-icon"><svg class="i" viewBox="0 0 24 24"><use href="#i-upload"/></svg></div>
                <h2>No splats yet</h2>
                <p class="muted">Mulai dengan mengunggah Gaussian Splat pertamamu.</p>
                <a class="btn btn-accent" href="{{ route('manage.create') }}">Upload Splat</a>
            </div>
        @else
            <div class="manage-list">
                @foreach ($splats as $splat)
                    <div class="manage-row">
                        <a class="manage-thumb" href="{{ route('splat.show', $splat) }}">
                            @if ($splat->thumbnailUrl())
                                <img src="{{ $splat->thumbnailUrl() }}" alt="{{ $splat->title }}">
                            @else
                                <div class="placeholder">{{ strtoupper(substr($splat->title, 0, 1)) }}</div>
                            @endif
                        </a>
                        <div class="manage-info">
                            <a href="{{ route('splat.show', $splat) }}"><strong>{{ $splat->title }}</strong></a>
                            <div class="muted small">
                                {{ strtoupper($splat->format) }} &middot;
                                {{ number_format($splat->file_size / 1048576, 1) }} MB &middot;
                                <svg class="i" viewBox="0 0 24 24"><use href="#i-eye"/></svg> {{ number_format($splat->views) }} &middot;
                                {{ $splat->is_public ? 'Publik' : 'Privat' }} &middot;
                                {{ $splat->created_at->format('d M Y') }}
                            </div>
                        </div>
                        <div class="manage-actions">
                            <a class="btn" href="{{ route('manage.edit', $splat) }}">Edit</a>
                            <form method="POST" action="{{ route('manage.destroy', $splat) }}"
                                  onsubmit="return confirm('Hapus \'{{ $splat->title }}\'? File juga akan dihapus.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
@endsection
