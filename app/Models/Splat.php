<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Splat extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'description', 'author_name',
        'file_path', 'thumbnail_path', 'format', 'file_size',
        'views', 'is_public', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_public' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePublik(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
            $q->where('title', 'like', $like)
                ->orWhere('author_name', 'like', $like)
                ->orWhere('description', 'like', $like);
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'splat';
        $slug = $base;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }

    // pakai asset() (ikut host request), bukan Storage::url() yang terpaku APP_URL
    public function fileUrl(): string
    {
        return asset('storage/'.$this->file_path);
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail_path
            ? asset('storage/'.$this->thumbnail_path)
            : null;
    }

    public function displayAuthor(): string
    {
        return $this->author_name ?: ($this->user->name ?? 'Anonim');
    }

    /** Konfigurasi scene untuk js/viewer.js (di-inject sebagai window.sceneConfig). */
    public function viewerConfig(): array
    {
        return array_merge([
            'id' => $this->slug,
            'title' => $this->title,
            'src' => $this->fileUrl(),
        ], $this->settings ?? []);
    }

    public function deleteFiles(): void
    {
        Storage::disk('public')->delete(array_filter([
            $this->file_path,
            $this->thumbnail_path,
        ]));

        // hapus folder scene bila sudah kosong
        $dir = dirname($this->file_path);
        if ($dir !== '.' && Storage::disk('public')->exists($dir) &&
            Storage::disk('public')->files($dir) === []) {
            Storage::disk('public')->deleteDirectory($dir);
        }
    }
}
