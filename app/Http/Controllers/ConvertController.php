<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;

class ConvertController extends Controller
{
    // format input yang diterima CLI splat-transform
    private const INPUT_EXTENSIONS = ['ply', 'sog', 'spz', 'ksplat', 'splat'];

    public const OUTPUT_FORMATS = [
        'ply' => '.ply (Standard PLY)',
        'compressed.ply' => '.compressed.ply (Compressed PLY)',
        'sog' => '.sog (Super Optimized Gaussians)',
        'spz' => '.spz (Niantic SPZ)',
    ];

    public function show(): View
    {
        return view('convert', [
            'localEnabled' => config('features.local_convert'),
            'outputFormats' => self::OUTPUT_FORMATS,
            'maxMb' => config('features.convert_max_mb'),
        ]);
    }

    public function convert(Request $request): BinaryFileResponse
    {
        abort_unless(config('features.local_convert'), 404);

        $maxKb = config('features.convert_max_mb') * 1024;

        $request->validate([
            'file' => ['required', 'file', "max:$maxKb"],
            'format' => ['required', 'in:'.implode(',', array_keys(self::OUTPUT_FORMATS))],
            'filename' => ['nullable', 'string', 'max:100'],
            'decimate' => ['nullable', 'boolean'],
            'decimate_percent' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $file = $request->file('file');
        $original = strtolower($file->getClientOriginalName());

        $inputExt = collect(self::INPUT_EXTENSIONS)
            ->first(fn ($ext) => str_ends_with($original, ".$ext"));

        if ($inputExt === null) {
            throw ValidationException::withMessages([
                'file' => 'Format input tidak didukung. Gunakan: .'.implode(', .', self::INPUT_EXTENSIONS),
            ]);
        }

        $this->cleanupStale();

        $workDir = storage_path('app/convert-tmp/'.Str::uuid());
        File::ensureDirectoryExists($workDir);

        // pertahankan sufiks ".compressed.ply" agar CLI mengenali formatnya
        $inputName = str_ends_with($original, '.compressed.ply')
            ? 'input.compressed.ply'
            : "input.$inputExt";
        $file->move($workDir, $inputName);

        $baseName = Str::slug($request->input('filename') ?: 'output') ?: 'output';
        $outputName = $baseName.'.'.$request->input('format');
        $outputPath = "$workDir/$outputName";

        $command = config('features.splat_transform_command').' -w';
        if ($request->boolean('decimate')) {
            $command .= ' "$INPUT" -F '.((int) $request->input('decimate_percent', 50)).'% "$OUTPUT"';
        } else {
            $command .= ' "$INPUT" "$OUTPUT"';
        }

        $env = [
            'INPUT' => "$workDir/$inputName",
            'OUTPUT' => $outputPath,
        ];
        // `artisan serve` tidak meneruskan PATH ke proses server, sehingga
        // shebang "#!/usr/bin/env node" gagal — teruskan PATH secara eksplisit
        if ($path = getenv('PATH') ?: ($_SERVER['PATH'] ?? null)) {
            $env['PATH'] = $path;
        }

        $process = Process::fromShellCommandline($command, $workDir, null, null, 600);
        $process->run(null, $env);

        if (! $process->isSuccessful() || ! is_file($outputPath)) {
            File::deleteDirectory($workDir);
            $detail = Str::limit(trim($process->getErrorOutput() ?: $process->getOutput()), 400);

            throw ValidationException::withMessages([
                'file' => 'Konversi gagal. '.($detail ?: 'Pastikan file input valid.')
                    .' (Bila error menyebut node/npx: pastikan Node.js ter-install di server,'
                    .' atau set SPLAT_TRANSFORM_COMMAND di .env ke path lengkap CLI splat-transform.)',
            ]);
        }

        File::delete("$workDir/$inputName");

        return response()->download($outputPath, $outputName)->deleteFileAfterSend(true);
    }

    /** Bersihkan folder kerja sisa konversi lama (mis. proses gagal terunduh). */
    private function cleanupStale(): void
    {
        $root = storage_path('app/convert-tmp');
        if (! is_dir($root)) {
            return;
        }

        foreach (File::directories($root) as $dir) {
            if (filemtime($dir) < time() - 3600) {
                File::deleteDirectory($dir);
            }
        }
    }
}
