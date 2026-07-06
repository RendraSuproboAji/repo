<?php

namespace Database\Seeders;

use App\Models\Splat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo', 'password' => 'password'],
        );

        if (Splat::where('slug', 'demo-galaxy')->exists()) {
            return;
        }

        // salin aset demo ke storage publik
        $assetDir = database_path('seeders/assets/demo-galaxy');
        $disk = Storage::disk('public');
        $disk->makeDirectory('splats/demo-galaxy');
        $disk->put('splats/demo-galaxy/scene.ply', file_get_contents($assetDir.'/scene.ply'));
        $disk->put('splats/demo-galaxy/thumbnail.jpg', file_get_contents($assetDir.'/thumbnail.jpg'));

        Splat::create([
            'user_id' => $user->id,
            'title' => 'Demo Galaxy',
            'slug' => 'demo-galaxy',
            'description' => 'Galaksi spiral berisi ±35.000 gaussian, dibuat prosedural oleh tools/generate_demo_splat.py sebagai contoh isi galeri.',
            'author_name' => 'Generated demo',
            'file_path' => 'splats/demo-galaxy/scene.ply',
            'thumbnail_path' => 'splats/demo-galaxy/thumbnail.jpg',
            'format' => 'ply',
            'file_size' => $disk->size('splats/demo-galaxy/scene.ply'),
            'is_public' => true,
            'settings' => [
                'camera' => [
                    'position' => [3.6, 2.4, 3.6],
                    'target' => [0, 0, 0],
                ],
            ],
        ]);
    }
}
