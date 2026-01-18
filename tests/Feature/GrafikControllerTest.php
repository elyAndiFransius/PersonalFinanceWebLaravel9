<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GrafikControllerTest extends TestCase
{
    /*
    |--------------------------------------------------------------------------
    | Controller test indexKategori
    |--------------------------------------------------------------------------
    */
    use RefreshDatabase;
    public function test_guest_cannot_access_grafik_kategori()
    {
        $this->getJson('/api/grafik/kategori')
            ->assertStatus(401) // karena controller tidak set status code
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_user_with_no_budget_gets_empty_response()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/grafik/kategori');

        $response->assertStatus(200);
        $this->assertEmpty($response->json());
    }

    public function test_user_can_get_grafik_kategori_data()
    {
        $user = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'pemasukkan' => 3000000,
            'priode' => 'Bulanan'
        ]);

        Category::factory()->create([
            'budget_id' => $budget->id,
            'name' => 'Makan',
            'jumlah' => 1000000
        ]);

        Category::factory()->create([
            'budget_id' => $budget->id,
            'name' => 'Transport',
            'jumlah' => 500000
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/grafik/kategori');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'kategori',
                    'jumlah',
                    'pemasukkan',
                    'priode'
                ]
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Controller test indexTarget
    |--------------------------------------------------------------------------
    */
    public function test_guest_cannot_access_index_target()
    {
        $this->getJson('/api/grafik/target')
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
    public function test_user_without_target_gets_not_found_message()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/grafik/target')
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Target tidak di temukan'
            ]);
    }
        public function test_user_can_get_target_data()
    {
        $user = User::factory()->create();

        Target::factory()->create([
            'user_id' => $user->id,
            'gol' => 'A',
            'targetAmount' => 5000000,
            'currentAmount' => 1500000,
        ]);

        $this->actingAs($user)
            ->getJson('/api/grafik/target')
            ->assertStatus(200)
            ->assertJson([
                'gol' => 'A',
                'targetAmount' => 5000000,
                'currentAmount' => 1500000,
            ]);
    }

}
