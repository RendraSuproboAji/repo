<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('splats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('author_name')->nullable();
            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('format', 32);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedBigInteger('views')->default(0);
            $table->boolean('is_public')->default(true);
            // pengaturan viewer per-scene: camera {position, target, fov},
            // rotation, position, scale — sama seperti scenes/index.json versi statis
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('views');
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splats');
    }
};
