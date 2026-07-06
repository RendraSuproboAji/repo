# Splat Gallery (Laravel)

Aplikasi galeri **3D Gaussian Splatting** ala [superspl.at](https://superspl.at/), dibangun dengan **Laravel 13 + SQLite**. Pengguna bisa mendaftar, mengunggah scene splat, mengelolanya di halaman *Your Splats*, dan semua scene publik tampil di galeri *Explore* dengan pencarian + urutan Trending/Terbaru. Viewer 3D interaktif (putar/geser/zoom) ditenagai engine [PlayCanvas](https://playcanvas.com) yang sudah di-vendor — tanpa build step Node/Vite.

> Versi **situs statis** (tanpa backend, bisa di-hosting di GitHub Pages) ada di branch `claude/supersplat-gallery-display-mebhfh`.

## Fitur

- **Explore** (`/`) — grid kartu scene publik dari database: thumbnail, judul, author, jumlah views; pencarian dan sort Trending (views) / Terbaru.
- **Viewer** (`/s/{slug}`) — viewer 3D gaussian splat (`.ply`, `.compressed.ply`, `.sog`) dengan orbit/pan/zoom, auto-framing, fullscreen; views bertambah otomatis.
- **Auth** — Sign Up / Login / Logout (session, tanpa starter kit).
- **Your Splats** (`/manage`) — unggah scene (+ thumbnail, deskripsi, publik/privat, opsi balik 180°), edit, dan hapus.
- **Convert** (`/convert`) — dua alternatif: konversi **di server** memakai CLI [splat-transform](https://github.com/playcanvas/splat-transform) (input `.ply/.compressed.ply/.sog/.spz/.ksplat/.splat`, output `.ply/.compressed.ply/.sog/.spz`, opsi decimate) **atau** link ke konverter resmi superspl.at. Konversi lokal bisa dihidup/matikan lewat `FEATURE_LOCAL_CONVERT`.
- **Menu drawer** ala superspl.at — Explore, Editor, Convert, Your Splats, Send feedback, GitHub, Discord, Sign Up/Login/Logout (item menyesuaikan status login; tautan diatur di `config/site.php`).

## Menjalankan secara lokal

Prasyarat: PHP ≥ 8.2 (ekstensi `pdo_sqlite`), Composer. Node.js hanya diperlukan untuk fitur konversi lokal.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed        # membuat tabel + scene demo & user demo
php artisan storage:link          # symlink public/storage
php artisan serve
```

Buka http://localhost:8000. Akun demo: `demo@example.com` / `password`.

Untuk unggahan besar, naikkan batas PHP saat dev:

```bash
php -d upload_max_filesize=512M -d post_max_size=520M artisan serve
```

### Mengaktifkan konversi lokal

```bash
npm install -g @playcanvas/splat-transform
```

lalu di `.env`:

```env
FEATURE_LOCAL_CONVERT=true
# default memakai npx; bisa juga path lengkap hasil `which splat-transform`
SPLAT_TRANSFORM_COMMAND="npx --yes @playcanvas/splat-transform"
```

Catatan: `php artisan serve` tidak meneruskan `PATH` ke proses servernya — bila muncul error `env: 'node': No such file or directory`, isi `SPLAT_TRANSFORM_COMMAND` dengan path lengkap CLI-nya.

## Konfigurasi

| Variabel `.env` | Keterangan |
|---|---|
| `FEATURE_LOCAL_CONVERT` | `true/false` — form konversi lokal di halaman Convert |
| `SPLAT_TRANSFORM_COMMAND` | Perintah CLI splat-transform |
| `CONVERT_MAX_MB` / `UPLOAD_MAX_MB` | Batas ukuran file konversi / unggahan (MB) |
| `SITE_LINK_EDITOR/GITHUB/FEEDBACK/DISCORD` | Tautan menu drawer (lihat `config/site.php`) |
| `DB_CONNECTION` | Default `sqlite`; ganti ke `mysql`/`pgsql` beserta kredensialnya bila perlu |

## Alur kerja konten

1. Buat/edit scene di [SuperSplat editor](https://superspl.at/editor), ekspor **Compressed PLY** atau **SOG** (jauh lebih kecil dari PLY biasa) — atau kompres lewat halaman **Convert**.
2. Login → **Upload Splat** → isi judul/deskripsi/thumbnail → simpan.
3. Scene publik langsung tampil di Explore; kelola kapan saja lewat **Your Splats**.

Scene demo (`demo-galaxy`) dibuat prosedural oleh `tools/generate_demo_splat.py`; hapus lewat halaman manage bila tidak diperlukan.

## Deployment

Butuh hosting PHP (shared hosting, VPS, Forge, dsb.) dengan document root diarahkan ke `public/`. Jangan lupa: `php artisan storage:link`, set `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`, dan naikkan `upload_max_filesize`/`post_max_size` di `php.ini`. GitHub Pages **tidak** bisa menjalankan Laravel — gunakan branch situs statis untuk itu.

## Struktur penting

```
app/Http/Controllers/   → Explore, SplatView, Auth, Manage, Convert
app/Models/Splat.php    → model scene (slug, file, settings kamera, views)
config/features.php     → feature flag konversi lokal
config/site.php         → tautan menu drawer
resources/views/        → Blade: layouts/app, explore, viewer, manage/*, auth/*, convert
public/lib/playcanvas.mjs → engine PlayCanvas (vendored, lisensi MIT)
public/js/viewer.js     → viewer 3D (konfigurasi via window.sceneConfig)
database/seeders/       → user demo + scene demo-galaxy
```

## Test

```bash
php artisan test
```

## Kredit

- [SuperSplat](https://github.com/playcanvas/supersplat) — editor 3D Gaussian Splat
- [splat-transform](https://github.com/playcanvas/splat-transform) — CLI konversi/kompresi splat
- [PlayCanvas Engine](https://github.com/playcanvas/engine) — renderer (lisensi MIT, lihat `public/lib/PLAYCANVAS-LICENSE`)
- [Laravel](https://laravel.com)
