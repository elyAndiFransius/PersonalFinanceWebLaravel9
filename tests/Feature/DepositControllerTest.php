<?php

namespace Tests\Feature;

use App\Models\Deposit;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DepositControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Controller test Index
    |--------------------------------------------------------------------------
    */

    public function test_user_can_get_deposit_list()
    {
        $user = User::factory()->create();

        Deposit::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/deposit');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Berikut ini adalah datanya'
            ]);
    }

    public function test_guest_cannot_access_deposit()
    {
        $this->getJson('/api/deposit')
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Controller test store
    |--------------------------------------------------------------------------
    */

    public function test_store_deposit_fails_if_target_not_found()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/deposit/store', [
                'date' => now()->toDateString(),
                'deposit' => 100000
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Target tidak ditemukan',
            ]);
    }

    public function test_store_deposit_when_target_already_reached()
    {
        $user = User::factory()->create();

        Target::factory()->create([
            'user_id' => $user->id,
            'targetAmount' => 1000000,
            'currentAmount' => 1000000,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/deposit/store', [
                'date' => now()->toDateString(),
                'deposit' => 100000
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Anda sudah melebih target yang diinginkan',
            ]);
    }

    public function test_store_deposit_validation_error()
    {
        $user = User::factory()->create();

        Target::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/deposit/store', [
                'date' => 'bukan-tanggal',
                'deposit' => -100
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false
            ])
            ->assertJsonStructure([
                'errors' => ['date', 'deposit']
            ]);
    }

    public function test_user_can_store_deposit_successfully()
    {
        $user = User::factory()->create();

        $target = Target::factory()->create([
            'user_id' => $user->id,
            'currentAmount' => 1000000,
            'targetAmount' => 5000000,
        ]);

        $payload = [
            'date' => now()->toDateString(),
            'deposit' => 500000
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/deposit/store', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Deposit disimpan',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'date', 'deposit']
            ]);

        // pastikan deposit masuk DB
        $this->assertDatabaseHas('deposits', [
            'user_id' => $user->id,
            'deposit' => 500000
        ]);

        // pastikan target bertambah
        $this->assertDatabaseHas('targets', [
            'id' => $target->id,
            'currentAmount' => 1500000
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Controller test Update
    |--------------------------------------------------------------------------
    */
    public function test_update_deposit_success()
    {
        $user = User::factory()->create();

        $target = Target::factory()->create([
            'user_id' => $user->id,
            'targetAmount' => 1000000,
            'currentAmount' => 300000,
        ]);

        $deposit = Deposit::factory()->create([
            'user_id' => $user->id,
            'deposit' => 100000,
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/deposit/update/{$deposit->id}", [
                'deposit' => 200000,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Deposit telah diupdate',
            ]);

        $this->assertDatabaseHas('deposits', [
            'id' => $deposit->id,
            'deposit' => 200000,
        ]);

        // 300000 - 100000 + 200000 = 400000
        $this->assertDatabaseHas('targets', [
            'id' => $target->id,
            'currentAmount' => 400000,
        ]);
    }

    public function test_update_deposit_forbidden_for_other_user()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Target::factory()->create([
            'user_id' => $owner->id,
        ]);

        $deposit = Deposit::factory()->create([
            'user_id' => $owner->id,
            'deposit' => 100000,
        ]);

        $response = $this->actingAs($otherUser)
            ->putJson("/api/deposit/update/{$deposit->id}", [
                'deposit' => 200000,
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Kamu tidak punya akses ke transaksi ini',
            ]);
    }

    public function test_update_deposit_fails_if_target_not_found()
    {
        $user = User::factory()->create();

        $deposit = Deposit::factory()->create([
            'user_id' => $user->id,
            'deposit' => 100000,
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/deposit/update/{$deposit->id}", [
                'deposit' => 200000,
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Target dana tidak ditemukan untuk pengguna ini',
            ]);
    }

    public function test_update_deposit_validation_error()
    {
        $user = User::factory()->create();

        Target::factory()->create([
            'user_id' => $user->id,
        ]);

        $deposit = Deposit::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/deposit/update/{$deposit->id}", [
                'deposit' => -100,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'errors',
            ]);
    }

    public function test_update_deposit_exceeds_target()
    {
        $user = User::factory()->create();

        Target::factory()->create([
            'user_id' => $user->id,
            'targetAmount' => 300000,
            'currentAmount' => 200000,
        ]);

        $deposit = Deposit::factory()->create([
            'user_id' => $user->id,
            'deposit' => 50000,
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/deposit/update/{$deposit->id}", [
                'deposit' => 200000,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'message' => 'Anda sudah melebih target yang diinginkan',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Controller test Delete
    |--------------------------------------------------------------------------
    */
    public function test_delete_deposit_success()
    {
        $user = User::factory()->create();

        $target = Target::factory()->create([
            'user_id' => $user->id,
            'currentAmount' => 500000,
        ]);

        $deposit = Deposit::factory()->create([
            'user_id' => $user->id,
            'deposit' => 200000,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/deposit/delete/{$deposit->id}");

        $response->assertStatus(200)
            ->assertJson([
                'seccess' => true,
                'message' => 'Data deposit telah di hapus',
            ]);

        // deposit benar-benar terhapus
        $this->assertDatabaseMissing('deposits', [
            'id' => $deposit->id,
        ]);

        // 500000 - 200000 = 300000
        $this->assertDatabaseHas('targets', [
            'id' => $target->id,
            'currentAmount' => 300000,
        ]);
    }

    public function test_delete_deposit_forbidden_for_other_user()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Target::factory()->create([
            'user_id' => $owner->id,
        ]);

        $deposit = Deposit::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/deposit/delete/{$deposit->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Kamu tidak punya akses untuk deposit ini',
            ]);
    }

    public function test_guest_cannot_delete_deposit()
    {
        $deposit = Deposit::factory()->create();

        $response = $this->deleteJson("/api/deposit/delete/{$deposit->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

}
