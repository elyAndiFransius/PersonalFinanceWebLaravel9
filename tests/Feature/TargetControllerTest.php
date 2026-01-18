<?php

namespace Tests\Feature;

use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TargetControllerTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Controller test index
    |--------------------------------------------------------------------------
    */
    public function test_guest_gets_empty_target_message()
    {
        $this->getJson('/api/targets')
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_user_without_target_gets_empty_message()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/targets')
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Target anda belum ada',
            ]);
    }

    public function test_user_can_get_target_list()
    {
        $user = User::factory()->create();

        Target::factory()->create([
            'user_id' => $user->id,
            'file' => 'dokumen.pdf',
        ]);

        $this->actingAs($user)
            ->getJson('/api/targets')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar target Anda',
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'gol',
                        'targetAmount',
                        'currentAmount',
                        'startDate',
                        'endDate',
                        'file',
                    ]
                ]
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Controller test store
    |--------------------------------------------------------------------------
    */
    public function test_store_target_without_login()
    {
        $response = $this->postJson('/api/targets/store', []);

        $response->assertStatus(401);
    }

    public function test_store_target_when_target_already_exists()
    {
        $user = User::factory()->create();

        Target::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/targets/store', [
                'gol' => 'Motor',
                'targetAmount' => 10000000,
                'currentAmount' => 0,
                'startDate' => now()->toDateString(),
                'endDate' => now()->addMonth()->toDateString(),
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Kamu sudah memiliki Target!',
            ]);
    }

    public function test_store_target_validation_failed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/targets/store', [
                // sengaja dikosongkan
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonStructure([
                'data' => [
                    'gol',
                    'targetAmount',
                    'currentAmount',
                    'startDate',
                    'endDate',
                ]
            ]);
    }

    public function test_user_can_create_target_successfully()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/api/targets/store', [
                'gol' => 'Rumah',
                'targetAmount' => 50000000,
                'currentAmount' => 1000000,
                'startDate' => now()->toDateString(),
                'endDate' => now()->addMonths(6)->toDateString(),
                'file' => UploadedFile::fake()->create(
                    'target.jpg',
                    100,
                    'image/jpeg'
                ),
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data Target berhasil di tambahkan',
            ]);

        $this->assertDatabaseHas('targets', [
            'user_id' => $user->id,
            'gol' => 'Rumah',
            'targetAmount' => 50000000,
            'currentAmount' => 1000000,
        ]);

        $target = Target::where('user_id', $user->id)->first();

        Storage::disk('public')->assertExists('uploads/' . $target->file);
    }

    /*
    |--------------------------------------------------------------------------
    | Controller test Update
    |--------------------------------------------------------------------------
    */
    public function test_update_target_requires_authentication()
    {
        $response = $this->putJson('/api/targets/update', [
            'gol' => 'Emas',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_user_can_update_target_successfully_without_file()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $target = Target::factory()->create([
            'user_id' => $user->id,
            'gol' => 'Rumah',
            'targetAmount' => 50000000,
            'currentAmount' => 1000000,
        ]);

        $response = $this->actingAs($user)
            ->put('/api/targets/update', [
                'gol' => 'Mobil',
                'targetAmount' => 75000000,
                'currentAmount' => 5000000,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Target berhasil diupdate',
            ]);

        $this->assertDatabaseHas('targets', [
            'id' => $target->id,
            'gol' => 'Mobil',
            'targetAmount' => 75000000,
            'currentAmount' => 5000000,
        ]);
    }
    public function test_user_can_update_target_and_replace_file()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // File lama
        $oldFile = UploadedFile::fake()->create(
            'old.jpg',
            100,
            'image/jpeg'
        );

        $oldFilename = 'old_target.jpg';
        Storage::disk('public')->putFileAs('uploads', $oldFile, $oldFilename);

        $target = Target::factory()->create([
            'user_id' => $user->id,
            'file' => $oldFilename,
        ]);

        $response = $this->actingAs($user)
            ->put('/api/targets/update', [
                'gol' => 'Tanah',
                'file' => UploadedFile::fake()->create(
                    'new.jpg',
                    200,
                    'image/jpeg'
                ),
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Target berhasil diupdate',
            ]);

        $target->refresh();

        // File lama terhapus
        Storage::disk('public')->assertMissing(
            'uploads/' . $oldFilename
        );

        // File baru tersimpan
        Storage::disk('public')->assertExists(
            'uploads/' . $target->file
        );
    }

    public function test_update_target_fails_if_user_has_no_target()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put('/api/targets/update', [
                'gol' => 'Emas',
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Anda Belum memiliki target',
            ]);
    }
    /*
    |--------------------------------------------------------------------------
    | Controller test destory
    |--------------------------------------------------------------------------
    */
    public function test_destroy_fails_when_user_not_authenticated()
    {
        $target = Target::factory()->create();

        $response = $this->deleteJson("/api/targets/destory/{$target->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_destroy_fails_when_target_not_owned_by_user()
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $target = Target::factory()->create([
            'user_id' => $userA->id
        ]);

        $response = $this->actingAs($userB)->deleteJson("/api/targets/destory/{$target->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'messege' => 'Kamu tidak punya akses untuk target ini.'
            ]);
    }
    public function test_destroy_success_when_target_owned_by_user()
    {
        $user = User::factory()->create();

        $target = Target::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/targets/destory/{$target->id}");

        $response->assertStatus(200)
            ->assertJson([
                'succes' => true,
                'message' => 'Data target telah di hapus'
            ]);

        $this->assertDatabaseMissing('targets', [
            'id' => $target->id
        ]);
    }
}
