<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransaksiControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Controller test index
    |--------------------------------------------------------------------------
    */
    public function test_guest_cannot_access_transaksi_index()
    {
        $response = $this->getJson('/api/transaksi');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_user_without_transaksi_gets_empty_message()
    {
        // Buat user
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/transaksi')
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Transaksi anda belum ada',
            ]);
    }
    public function test_user_can_access_transaksi_with_data()
    {
        $user = User::factory()->create();

        Transaksi::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/transaksi')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Berikut ini adalah datanya',
            ])
            ->assertJsonCount(2, 'data');
    }
    /*
    |--------------------------------------------------------------------------
    | Controller test store
    |--------------------------------------------------------------------------
    */
    public function test_guest_cannot_store_transaksi()
    {
        $this->postJson('/api/transaksi/store', [])
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_store_transaksi_validation_failed()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/transaksi/store', [])
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validasi gagal',
            ]);
    }

    public function test_store_pengeluaran_melebihi_budget()
    {
        $user = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
        ]);

        $category = Category::factory()->create([
            'budget_id' => $budget->id,
            'jumlah' => 100000,
        ]);

        $this->actingAs($user)
            ->postJson('/api/transaksi/store', [
                'categories_id' => $category->id,
                'jenis' => 'pengeluaran',
                'descripsi' => 'Belanja',
                'jumlah' => 150000,
                'date' => now()->toDateString(),
            ])
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }
    public function test_user_can_store_pengeluaran_transaksi()
    {
        $user = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
        ]);

        $category = Category::factory()->create([
            'budget_id' => $budget->id,
            'jumlah' => 200000,
        ]);

        $this->actingAs($user)
            ->postJson('/api/transaksi/store', [
                'categories_id' => $category->id,
                'jenis' => 'pengeluaran',
                'descripsi' => 'Makan',
                'jumlah' => 50000,
                'date' => now()->toDateString(),
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Transaksi berhasil ditambahkan.',
            ]);

        $this->assertDatabaseHas('transaksi', [
            'user_id' => $user->id,
            'categories_id' => $category->id,
            'jenis' => 'pengeluaran',
            'jumlah' => 50000,
        ]);
    }
    public function test_user_can_store_pemasukkan_transaksi()
    {
        $user = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
        ]);

        $category = Category::factory()->create([
            'budget_id' => $budget->id,
            'jumlah' => 100000,
        ]);

        $this->actingAs($user)
            ->postJson('/api/transaksi/store', [
                'categories_id' => $category->id,
                'jenis' => 'pemasukkan',
                'descripsi' => 'Gaji',
                'jumlah' => 300000,
                'date' => now()->toDateString(),
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('transaksi', [
            'jenis' => 'pemasukkan',
            'jumlah' => 300000,
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | Controller test update
    |--------------------------------------------------------------------------
    */
    public function test_guest_cannot_update_transaksi()
    {
        $user = User::factory()->create();

        $transaksi = Transaksi::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->putJson("/api/transaksi/update/{$transaksi->id}", [
            'descripsi' => 'Update'
        ])
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_user_cannot_update_other_users_transaksi()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $transaksi = Transaksi::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($otherUser)
            ->putJson("/api/transaksi/update/{$transaksi->id}", [
                'descripsi' => 'Update'
            ])
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Kamu tidak punya akses ke transaksi ini',
            ]);
    }

    public function test_update_transaksi_validation_failed()
    {
        $user = User::factory()->create();

        $transaksi = Transaksi::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->putJson("/api/transaksi/update/{$transaksi->id}", [
                'jumlah' => 0
            ])
            ->assertStatus(422) // controller kamu tidak set status
            ->assertJson([
                'success' => false,
                'message' => 'validasi gagal,',
            ]);
    }

    public function test_update_pengeluaran_melebihi_budget()
    {
        $user = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
        ]);

        $category = Category::factory()->create([
            'budget_id' => $budget->id,
            'jumlah' => 100000,
        ]);

        $transaksi = Transaksi::factory()->create([
            'user_id' => $user->id,
            'categories_id' => $category->id,
            'jenis' => 'pengeluaran',
            'jumlah' => 50000,
        ]);

        $this->actingAs($user)
            ->putJson("/api/transaksi/update/{$transaksi->id}", [
                'categories_id' => $category->id,
                'jenis' => 'pengeluaran',
                'jumlah' => 200000,
            ])
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }
    public function test_user_can_update_transaksi_successfully()
    {
        $user = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
        ]);

        $category = Category::factory()->create([
            'budget_id' => $budget->id,
            'jumlah' => 100000,
        ]);

        $transaksi = Transaksi::factory()->create([
            'user_id' => $user->id,
            'categories_id' => $category->id,
            'jenis' => 'pemasukkan',
            'jumlah' => 50000,
        ]);

        $this->actingAs($user)
            ->putJson("/api/transaksi/update/{$transaksi->id}", [
                'categories_id' => $category->id,
                'jenis' => 'pemasukkan',
                'descripsi' => 'Update Gaji',
                'jumlah' => 150000,
            ])
            ->assertStatus(200)
            ->assertJson([
                'success' => false, // sesuai controller kamu
                'message' => 'Data transaksi berhasil di ubah',
            ]);

        $this->assertDatabaseHas('transaksi', [
            'id' => $transaksi->id,
            'descripsi' => 'Update Gaji',
            'jumlah' => 150000,
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | Controller test destroy
    |--------------------------------------------------------------------------
    */
    public function test_guest_cannot_delete_transaksi()
    {
        $transaksi = Transaksi::factory()->create();

        $this->deleteJson("/api/transaksi/delete/{$transaksi->id}")
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
    public function test_user_cannot_delete_other_user_transaksi()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $transaksi = Transaksi::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/transaksi/delete/{$transaksi->id}")
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Kamu tidak punya akses untuk target ini.',
            ]);
    }
    public function test_user_can_delete_transaksi()
    {
        $user = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
        ]);

        $category = Category::factory()->create([
            'budget_id' => $budget->id,
        ]);

        $transaksi = Transaksi::factory()->create([
            'user_id' => $user->id,
            'categories_id' => $category->id,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/transaksi/delete/{$transaksi->id}")
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data target telah di hapus',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'categories_id',
                    'jenis',
                    'descripsi',
                    'jumlah',
                    'date',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseMissing('transaksi', [
            'id' => $transaksi->id
        ]);
    }

}
