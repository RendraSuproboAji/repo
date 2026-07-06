<?php

namespace Tests\Feature;

use App\Models\Splat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_explore_page_loads(): void
    {
        $this->get('/')->assertStatus(200)->assertSee('Splat');
    }

    public function test_guest_is_redirected_from_manage(): void
    {
        $this->get('/manage')->assertRedirect('/login');
    }

    public function test_public_splat_is_listed_and_viewable(): void
    {
        $user = User::factory()->create();
        $splat = Splat::create([
            'user_id' => $user->id,
            'title' => 'Contoh Scene',
            'slug' => 'contoh-scene',
            'file_path' => 'splats/contoh-scene/scene.ply',
            'format' => 'ply',
            'is_public' => true,
        ]);

        $this->get('/')->assertSee('Contoh Scene');
        $this->get('/s/contoh-scene')->assertStatus(200);
        $this->assertSame(1, $splat->fresh()->views);
    }

    public function test_private_splat_hidden_from_others(): void
    {
        $user = User::factory()->create();
        Splat::create([
            'user_id' => $user->id,
            'title' => 'Rahasia',
            'slug' => 'rahasia',
            'file_path' => 'splats/rahasia/scene.ply',
            'format' => 'ply',
            'is_public' => false,
        ]);

        $this->get('/')->assertDontSee('Rahasia');
        $this->get('/s/rahasia')->assertStatus(404);
        $this->actingAs($user)->get('/s/rahasia')->assertStatus(200);
    }
}
