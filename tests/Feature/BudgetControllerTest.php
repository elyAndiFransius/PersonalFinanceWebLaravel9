<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;
use App\Models\User;
use App\Models\Budget;
use function PHPUnit\Framework\assertJson;

class BudgetControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function test_user_can_get_budget_data()
    {
        // Siapkan
        $user = User::factory()->create();
        // hanya satu budget
        Budget::factory()->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user)
            ->getJson('/api/budgets')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'pemasukkan', 'priode', 'categories']
                ]
            ]);
    }

    public function test_user_can_create_budget_with_category()
    {
        // Siapkan
        $user = User::factory()->create();

        $payload = [
            'pemasukkan' => 10000,
            'priode' => 'Bulanan',
            'categories' => [
                ['name' => 'Makan', 'jumlah' => 5000],
                ['name' => 'Transport', 'jumlah' => 5000],
            ]
        ];

        // Aksi
        $response = $this->actingAs($user)
            ->postJson('/api/budgets/create', $payload);

        // Verifikasi
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Mantap! Data budget dan kategori berhasil dibuat!',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'pemasukkan',
                    'priode',
                    'categories' => [
                        '*' => ['id', 'name', 'jumlah']
                    ]
                ]
            ]);
    }

    public function test_user_cannot_create_budget_if_already_exists()
    {
        // Siapkan
        $user = User::factory()->create();

        Budget::factory()->create([
            'user_id' => $user->id
        ]);

        // hanya satu budget
        $payload = [
            'pemasukkan' => 10000,
            'priode' => 'Bulanan',
            'categories' => [
                ['name' => 'Makan', 'jumlah' => 5000],
                ['name' => 'Transport', 'jumlah' => 5000],
            ]
        ];

        // Aksi
        $response = $this->actingAs($user)
            ->postJson('/api/budgets/create', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Kamu sudah memiliki Budget!',
            ]);
    }

    public function test_create_budget_validation_fails_if_categories_empty()
    {
        // Siapkan hanya usernya saja
        $user = User::factory()->create();

        // hanya satu budget
        $payload = [
            'pemasukkan' => 10000,
            'priode' => 'Bulanan',
            'categories' => []
        ];

        // Aksi
        $response = $this->actingAs($user)
            ->postJson('/api/budgets/create', $payload);

        // Assert
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Yah gagal, kategori kosong!',
            ])
            ->assertJsonStructure([
                'data' => ['categories']
            ]);
    }

    public function test_create_budget_fails_with_invalid_priode()
    {
        $user = User::factory()->create();

        $payload = [
            'pemasukkan' => 1000000,
            'priode' => 'Tahunanx',
            'categories' => [
                ['name' => 'Makan', 'jumlah' => 500000],
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/budgets/create', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_guest_cannot_create_budget()
    {
        $payload = [
            'pemasukkan' => 1000000,
            'priode' => 'Bulanan',
            'categories' => [
                ['name' => 'Makan', 'jumlah' => 500000],
            ]
        ];

        $this->postJson('/api/budgets/create', $payload)
            ->assertStatus(401);
    }

    public function test_user_can_update()
    {
        // Siapkan
        $user = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'pemasukkan' => 3000000,
            'priode' => 'Bulanan'
        ]);

        $payload = [
            'pemasukkan' => 4000000,
            'priode' => 'Tahunan'
        ];

        // aksi
        $response = $this->actingAs($user)
            ->putJson("/api/budgets/update/{$budget->id}", $payload);

        // Verifikasi
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Budget telah berhasil diupdate'
            ]);

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'pemasukkan' => 4000000,
            'priode' => 'Tahunan'
        ]);
    }
    public function test_user_cannot_update_other_users_budget()
    {
        // siapkan
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $owner->id
        ]);

        // Aksi & Verifikasi
        $this->actingAs($otherUser)
            ->putJson("/api/budgets/update/{$budget->id}", [
                'pemasukkan' => 1000000
            ])
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Kamu tidak punya akses ke budget ini.'
            ]);
    }

    public function test_update_budget_fails_with_invalid_priode()
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user)
            ->putJson("/api/budgets/update/{$budget->id}", [
                'priode' => 'BulananX'
            ])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal'
            ]);
    }

    public function test_update_budget_fails_when_category_exceeds_pemasukkan()
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'pemasukkan' => 1000000
        ]);

        $payload = [
            'categories' => [
                ['name' => 'Makan', 'jumlah' => 700000],
                ['name' => 'Transport', 'jumlah' => 600000],
            ]
        ];

        $this->actingAs($user)
            ->putJson("/api/budgets/update/{$budget->id}", $payload)
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Total jumlah kategori tidak boleh melebihi pemasukkan.'
            ]);
    }

    public function test_guest_cannot_update_budget()
    {
        // Siapkan datanya
        $budget = Budget::factory()->create();

        $this->putJson("/api/budgets/update/{$budget->id}", [
            'pemasukkan' => 2000000
        ])->assertStatus(401);

    }

    public function test_user_can_delete_own_budget()
    {
        // Siapkan
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id
        ]);

        // Aksi
        $response = $this->actingAs($user)
            ->deleteJson("/api/budgets/delete/{$budget->id}");

        // Verifikasi
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data dudgeting kamu berhasil di hapus'
            ]);

        $this->assertDatabaseMissing('budgets', [
            'id' => $budget->id
        ]);
    }

    public function test_user_cannot_delete_other_users_budget()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $owner->id
        ]);

        $this->actingAs($otherUser)
            ->deleteJson("/api/budgets/delete/{$budget->id}")
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Kamu tidak punya akses ke budget Ini.'
            ]);

        // Pastikan data masih ada
        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id
        ]);
    }


    public function test_guest_cannot_delete_budget()
    {
        $budget = Budget::factory()->create();

        $this->deleteJson("/api/budgets/delete/{$budget->id}")
            ->assertStatus(401);
    }






}
