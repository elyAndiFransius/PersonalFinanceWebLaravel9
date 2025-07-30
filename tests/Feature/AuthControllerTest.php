<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Mail\VerificationCodeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // test register
    /** @test */
    public function register_fail_when_data_is_invalid()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-email',
            'password' => '123',
            'confirm_password' => '321'
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Register gagal',
                 ])
                 ->assertJsonStructure([
                    'data' => ['name', 'email', 'password', 'confirm_password']
                    ]);
             
    }
    /** @test */
    public function register_successfully_create_user_and_sends_email()
    {
        // Arrange: Menonaktifkan pengiriman email secara nyata
        Mail::fake();

        // Act: Kirim data pendaftaran
        $response = $this->postJson('/api/register', [
            'name' => 'Test user',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'confirm_password' => 'secret123'
        ]);

        // Assert: Respon sukses dan email dikirim
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'success' => true,
                     'message' => 'Berhasil Membuat akun'
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com'
        ]);

        Mail::assertSent(VerificationCodeEmail::class);
    }

    // test login
    /** @test */
    public function test_login_fails_when_user_not_verified()
    {
        // Arrange: Buat user belum diverifikasi
        $user = User::factory()->create([
            'email' => 'belum@verifikasi.com',
            'password' => bcrypt('secret123'),
            'verification' => false,
            'token_code' => '123456'
        ]);

        // Act: Kirim permintaan login
        $response = $this->postJson('/api/login', [
            'email' => 'belum@verifikasi.com',
            'password' => 'secret123',
        ]);

        // Assert: Login gagal karena belum verifikasi
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Akun belum diverifikasi. Periksa email untuk kode OTP.'
        ]);
    }
    
    /** @test */
    public function test_login_success_when_user_verified(){
        // Arrange: Buat user yang sudah diverifikasi
        $user = User::factory()->create([
            'email' => 'sudah@verifikasi.com',
            'password' => bcrypt('secret123'),
            'verification' => true,
            'token_code' => '123456',
        ]);

        // Act: Kirim permintaan login
        $response = $this->postJson('/api/login', [
            'email' => 'sudah@verifikasi.com',
            'password' => 'secret123',
        ]);

        // Assert: Login berhasil
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'access_token',
            'token_type',
            'user',
        ]);
    }

    // test sendOtpForReset
    /** @test */
    public function sendOtpForReset_fail_when_email_not_found(){
        // Arrange: Pastikan tidak ada user
        Mail::fake();

        // Act: Kirim request OTP untuk email yang tidak ada
        $response = $this->postJson('/api/send-otp-for-reset', [
            'email' => 'tidakada@example.com',
            'password' => 'password123',
        ]);

        // Assert: Respon 404 dan tidak mengirim email
        $response->assertStatus(404)
                 ->assertJson([
                    'success' => false,
                    'message' => 'Email tidak ditemukan.',
                 ]);

        Mail::assertNothingSent();
    }
    /** @test */
    public function sendOtpForReset_successfully_sends_email_and_stores_temp_password()
    {
        // Arrange: Buat user yang sudah verifikasi
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('oldpassword'),
            'verification' => true,
            'token_code' => '123456'
        ]);

        Mail::fake();

        // Act: Kirim request reset password
        $response = $this->postJson('/api/send-otp-for-reset', [
            'email' => 'user@example.com',
            'password' => 'newpassword123',
        ]);

        // Assert: OTP dikirim dan temporary password disimpan
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Kode OTP telah dikirim ke email.',
                 ]);

        Mail::assertSent(VerificationCodeEmail::class, function ($mail) use ($user) {
            return $mail->hasTo('user@example.com');
        });

        $user->refresh();
        $this->assertNotNull($user->token_code);
        $this->assertTrue(Hash::check('newpassword123', $user->temporary_password));
    }

    // test verifyOtpForReset
    /** @test */
    public function test_verifyOtpForReset_fails_when_email_and_code_not_match()
    {
        // Arrange: Buat user dengan data tertentu
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'temporary_password' => bcrypt('newsecret123'),
            'token_code' => '123456'
        ]);

        // Act: Kirim request dengan email yang tidak cocok
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'belum@verifikasi.com', // email tidak cocok
            'code_verification' => 'kodeSalah',
        ]);

        // Assert: Gagal karena kombinasi email dan kode OTP tidak cocok
        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Kode OTP salah atau tidak ditemukan.'
        ]);
    }

    /** @test */
    public function test_verifyOtpForReset_success_when_user_not_verified()
    {
        // Arrange: Buat user yang belum diverifikasi dan punya OTP aktif
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'verification' => false,
            'temporary_password' => bcrypt('newsecret123'),
            'token_code' => '123456',
            'otp_sent_add' => now(),
        ]);

        // Act: Kirim request verifikasi OTP
        $response = $this->postJson('/api/forgot-password', [
            'email' => 'test@example.com',
            'code_verification' => '123456',
        ]);

        // Assert: Respon sukses dan password diperbarui
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login.'
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'token_code' => '',
            'temporary_password' => null,
            'verification' => true,
        ]);
    }

    // test validationKodeOTP
    /** @test */
    public function test_validationKodeOTP_fails_when_email_and_code_not_match()
    {
        // Arrange: Buat user dengan data tertentu
        $user = User::factory()->create([
            'email' => 'belum@verifikasi.com',
            'token_code' => '123456'
        ]);

        // Act: Kirim request dengan email yang tidak cocok
        $response = $this->postJson('/api/validationKodeOTP', [
            'email' => 'belum@verifikasi.com', // email tidak cocok
            'code_verification' => 'kodeSalah',
        ]);

        // Assert: Gagal karena kombinasi email dan kode OTP tidak cocok
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertSeeText('Kode OTP salah! Percobaan ke-');
    }
    /** @test */
    public function test_validationKodeOTP_successfuly_when_email_and_code_match()
    {
        // Arrange: Buat user dengan data tertentu
        $user = User::factory()->create([
            'email' => 'belum@verifikasi.com',
            'token_code' => '123456'
        ]);

        // Act: Kirim request dengan email yang tidak cocok
        $response = $this->postJson('/api/validationKodeOTP', [
            'email' => 'belum@verifikasi.com', // email tidak cocok
            'code_verification' => '123456',
        ]);

        // Assert: berhasil
        $this->assertDatabaseHas('users', [
            'email' => 'belum@verifikasi.com',
            'token_code' => '',
            'verification' => true,
        ]);
     
    }

    // test sendOTP
    /** @test */
    public function test_send_otp_fails_when_email_match()
    {

        // Arrange: Buat user dengan data tertentu
        $user = User::factory()->create([
            'email' => 'belumDikirim@verifikasi.com',
            'token_code' => '123123'
        ]);

        // Act: Kirim request dengan email yang tidak cocok
        $response = $this->postJson('/api/send-otp', [
            'email' => 'belum@verifikasi.com', // email tidak cocok
        ]);

        // Assert: Gagal karena kombinasi email dan kode OTP tidak cocok
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Email tidak di temukan'
        ]);

    }
    /** @test */
     public function test_sendOTP_succesfuly_when_email_match()
     {
        // Arrange: Menonaktifkan pengiriman email secara nyata
        Mail::fake();

         // Arrange: Buat user dengan data tertentu
         $user = User::factory()->create([
             'email' => 'contoh@verifikasi.com',
             'token_code' => '123456'
         ]);

         // Act: Kirim request dengan email yang cocok
         $response = $this->postJson('/api/send-otp', [
             'email' => 'contoh@verifikasi.com', // email tidak cocok
             'code_verification' => '123456',
         ]);

        // Assert: Respon sukses dan email dikirim
        $response->assertStatus(200)
                 ->assertJsonFragment([
                     'success' => true,
                     'message' => 'Kode kamu berhasil di kirimkan ke emaail kamu'
                 ]);
     }



}
