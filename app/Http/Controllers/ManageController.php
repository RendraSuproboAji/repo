<?php

namespace App\Http\Controllers;

use App\Models\Splat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ManageController extends Controller
{
    public function index(Request $request): View
    {
        $splats = $request->user()->splats()->latest()->get();

        return view('manage.index', compact('splats'));
    }

    public function create(): View
    {
        return view('manage.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $maxKb = config('features.upload_max_mb') * 1024;

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', "max:$maxKb"],
            'thumbnail' => ['nullable', 'image', 'max:5120'],
            'is_public' => ['nullable', 'boolean'],
            'flip' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $original = strtolower($file->getClientOriginalName());

        $format = match (true) {
            str_ends_with($original, '.compressed.ply') => 'compressed.ply',
            str_ends_with($original, '.ply') => 'ply',
            str_ends_with($original, '.sog') => 'sog',
            default => null,
        };

        if ($format === null) {
            throw ValidationException::withMessages([
                'file' => 'Format tidak didukung. Gunakan .ply, .compressed.ply, atau .sog (ekspor dari SuperSplat / splat-transform).',
            ]);
        }

        $slug = Splat::uniqueSlug($data['title']);
        $dir = "splats/$slug";
        $ext = str_ends_with($original, '.sog') ? 'sog' : 'ply';
        $filePath = $file->storeAs($dir, "scene.$ext", 'public');

        $thumbnailPath = $request->hasFile('thumbnail')
            ? $request->file('thumbnail')->storeAs(
                $dir,
                'thumbnail.'.$request->file('thumbnail')->extension(),
                'public')
            : null;

        $settings = $request->boolean('flip') ? ['rotation' => [180, 0, 0]] : null;

        $splat = Splat::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'author_name' => $request->user()->name,
            'file_path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'format' => $format,
            'file_size' => $file->getSize(),
            'is_public' => $request->boolean('is_public', true),
            'settings' => $settings,
        ]);

        return redirect()->route('splat.show', $splat)
            ->with('status', 'Splat berhasil diunggah.');
    }

    public function edit(Request $request, Splat $splat): View
    {
        abort_unless($splat->user_id === $request->user()->id, 403);

        return view('manage.edit', compact('splat'));
    }

    public function update(Request $request, Splat $splat): RedirectResponse
    {
        abort_unless($splat->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'thumbnail' => ['nullable', 'image', 'max:5120'],
            'is_public' => ['nullable', 'boolean'],
            'flip' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('thumbnail')) {
            $splat->thumbnail_path = $request->file('thumbnail')->storeAs(
                dirname($splat->file_path),
                'thumbnail.'.$request->file('thumbnail')->extension(),
                'public');
        }

        $settings = $splat->settings ?? [];
        if ($request->boolean('flip')) {
            $settings['rotation'] = [180, 0, 0];
        } else {
            unset($settings['rotation']);
        }

        $splat->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_public' => $request->boolean('is_public'),
            'settings' => $settings ?: null,
        ])->save();

        return redirect()->route('manage.index')->with('status', 'Splat diperbarui.');
    }

    public function destroy(Request $request, Splat $splat): RedirectResponse
    {
        abort_unless($splat->user_id === $request->user()->id, 403);

        $splat->deleteFiles();
        $splat->delete();

        return redirect()->route('manage.index')->with('status', 'Splat dihapus.');
    }
}
