@extends('layouts.app')

@section('title', 'Edit — '.config('app.name'))

@section('content')
    <main class="page-narrow">
        <div class="panel">
            <h1>Edit Splat</h1>

            <form method="POST" action="{{ route('manage.update', $splat) }}" enctype="multipart/form-data" class="form">
                @csrf
                @method('PUT')
                <div class="form-field">
                    <label for="title">Judul</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $splat->title) }}" required maxlength="120">
                    @error('title')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <div class="form-field">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" maxlength="2000">{{ old('description', $splat->description) }}</textarea>
                    @error('description')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <div class="form-field">
                    <label for="thumbnail">Ganti thumbnail (opsional)</label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                    @error('thumbnail')<div class="error-text">{{ $message }}</div>@enderror
                </div>
                <label class="check">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $splat->is_public))>
                    Tampilkan di galeri publik
                </label>
                <label class="check">
                    <input type="checkbox" name="flip" value="1"
                        @checked(old('flip', ($splat->settings['rotation'] ?? null) === [180, 0, 0]))>
                    Balik 180° (untuk PLY hasil training yang terbalik)
                </label>
                <button type="submit" class="btn btn-accent btn-block">Simpan</button>
            </form>

            <p class="muted"><a href="{{ route('manage.index') }}">&larr; Kembali ke Your Splats</a></p>
        </div>
    </main>
@endsection
