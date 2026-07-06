@extends('layouts.app')

@section('title', 'Convert — '.config('app.name'))

@section('content')
    <main class="page-narrow">
        <div class="page-head">
            <h1>Convert</h1>
        </div>
        <p class="muted">Konversi antar format Gaussian Splat. Pilih salah satu cara di bawah.</p>

        @if ($localEnabled)
            <div class="panel">
                <h2>Konversi di server ini</h2>
                <p class="muted small">
                    Didukung: input <code>.ply .compressed.ply .sog .spz .ksplat .splat</code>,
                    maks. {{ $maxMb }} MB. Ditenagai
                    <a href="https://github.com/playcanvas/splat-transform" target="_blank" rel="noopener">splat-transform</a>.
                </p>

                <form method="POST" action="{{ route('convert.run') }}" enctype="multipart/form-data" class="form">
                    @csrf
                    <div class="form-field">
                        <label for="file">Input file</label>
                        <input type="file" id="file" name="file" accept=".ply,.sog,.spz,.ksplat,.splat" required>
                        @error('file')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-field">
                        <label for="format">Format output</label>
                        <select id="format" name="format">
                            @foreach ($outputFormats as $value => $label)
                                <option value="{{ $value }}" @selected(old('format') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('format')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-field">
                        <label for="filename">Nama file output</label>
                        <input type="text" id="filename" name="filename" value="{{ old('filename', 'output') }}" maxlength="100">
                    </div>
                    <label class="check">
                        <input type="checkbox" name="decimate" value="1" @checked(old('decimate'))
                               onchange="document.getElementById('decimate-row').hidden = !this.checked">
                        Decimate — kurangi jumlah gaussian dengan menggabungkan yang mirip
                    </label>
                    <div class="form-field" id="decimate-row" @if(!old('decimate')) hidden @endif>
                        <label for="decimate_percent">Sisakan (% dari jumlah gaussian)</label>
                        <input type="number" id="decimate_percent" name="decimate_percent"
                               value="{{ old('decimate_percent', 50) }}" min="1" max="99">
                        @error('decimate_percent')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-accent btn-block">Convert &amp; Download</button>
                    <p class="muted small">Konversi berjalan di server; file hasil otomatis terunduh. File besar bisa memakan waktu ±1 menit.</p>
                </form>
            </div>
        @endif

        <div class="panel">
            <h2>Convert via superspl.at</h2>
            <p class="muted small">
                Alternatif: gunakan konverter resmi SuperSplat di browser — mendukung
                <code>.ply .sog .ksplat .splat .spz</code> tanpa mengunggah ke server ini.
            </p>
            <a class="btn btn-block" href="https://superspl.at/convert" target="_blank" rel="noopener">
                Buka superspl.at/convert ↗
            </a>
        </div>

        @if (! $localEnabled)
            <p class="muted small">
                Konversi lokal dinonaktifkan oleh administrator
                (<code>FEATURE_LOCAL_CONVERT=false</code>).
            </p>
        @endif
    </main>
@endsection
