<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_category_index()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/categories')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Kategori ditemukan',
            ]);
    }

    public function test_guest_cannot_access_category()
    {
        // Aksi dan verifikasi
        $this->getJson('api/categories/')
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

}
