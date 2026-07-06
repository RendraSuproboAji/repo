@extends('layouts.app')

@section('title', 'Upload Splat — '.config('app.name'))

@section('content')
    <main class="page-narrow">
        <div class="panel">
            <h1>Upload Splat</h1>

            <form method="POST" action="{{ route('manage.store') }}" enctype="multipart/form-data" class="form">
                @csrf
                <div class="form-field">
                    <label for="file">File splat (.ply / .compressed.ply / .sog, maks. {{ config('features.upload_max_mb') }} MB)</label>
                    <input type="file" id="file" name="file" accept=".ply,.sog" required>
                    @error('file')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <div class="form-field">
                    <label for="title">Judul</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required maxlength="120">
                    @error('title')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <div class="form-field">
                    <label for="description">Deskripsi (opsional)</label>
                    <textarea id="description" name="description" rows="4" maxlength="2000">{{ old('description') }}</textarea>
                    @error('description')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <div class="form-field">
                    <label for="thumbnail">Thumbnail (opsional, gambar maks. 5 MB)</label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                    @error('thumbnail')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <label class="check">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', true))>
                    Tampilkan di galeri publik
                </label>
                <label class="check">
                    <input type="checkbox" name="flip" value="1" @checked(old('flip'))>
                    Balik 180° (untuk PLY hasil training yang terbalik)
                </label>
                <button type="submit" class="btn btn-accent btn-block">Upload</button>
            </form>

            <p class="muted small">
                Tips: ekspor <em>Compressed PLY</em> atau <em>SOG</em> dari
                <a href="https://superspl.at/editor" target="_blank" rel="noopener">SuperSplat editor</a>,
                atau kompres lewat halaman <a href="{{ route('convert') }}">Convert</a> — ukurannya
                jauh lebih kecil daripada PLY biasa.
            </p>
        </div>
    </main>
@endsection
