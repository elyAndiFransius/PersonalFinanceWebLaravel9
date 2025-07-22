<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Mail\VerificationCodeEmail;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'confirm_password' => 'required|min:6|same:password'
        ]);

        $code_verification = rand(100000, 999999);
        
        // Jika tidak berhasil membuat akun mengembalikan nilai error
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Register gagal',
                'data' => $validator->errors()
            ]);
        }
        $input = $request->all(); // Menampung semua data yang di inputkan oleh pengguna
        $input['password'] = bcrypt($input['password']); // Melakukan enkripsi data password
        $input['otp_type'] ='register';
        $input['otp_sent_add'] = now();
        $input['token_code'] = $code_verification; 
        $user = User::create($input); // Menyimpan semua data yang di inputkan melalui variabel $input


       Mail::to($user ->email)->send(new VerificationCodeEmail($user, $code_verification));

 
        $success['token'] = $user->createToken('auth_token')->plainTextToken; //Membuat dan menampilkan token authtifikasi
        $success['name'] = $user->name; // menampilkan name

        // Jika berhasil menmapilkan data token dan namenya
        return response()->json([
            'success' => true,
            'message' => 'Berhasil Membuat akun',
            'data' => $success,
        ]);

       // return redirect()->route('verification.form')->with('email', $user->email);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        // Cek dulu apakah user ditemukan dan sudah verifikasi
        if (!$user || !$user->verification) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum diverifikasi. Periksa email untuk kode OTP.'
            ], 403);
        }

        // Coba login
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Login gagal, email atau password salah.'
            ], 401);
        }

        // Jika sukses login
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function sendOtpForReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak ditemukan.'
            ], 404);
        }

        $otp = rand(100000, 999999);
        $user->token_code = $otp;
        $user->temporary_password = Hash::make($request->password); // Simpan sementara
        $user->save();

        // Kirim OTP ke email
        Mail::to($user->email)->send(new VerificationCodeEmail($user, $otp));

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke email.'
        ]);
    }

    public function verifyOtpForReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code_verification' => 'required'
        ]);

        $user = User::where('email', $request->email)
                    ->where('token_code', $request->code_verification)
                    ->first();

        // Cek apakah user diblokir sementara
        if ($user->otp_attempts >= 5) {
            $lastAttempt = Carbon::parse($user->last_failed_otp);
            if ($lastAttempt->diffInMinutes(now()) < 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam 5 menit.'
                ], 429);
            } else {
                // Reset jika sudah lewat 5 menit
                $user->otp_attempts = 0;
                $user->save();
            }
        }
        // hapus token jika lewat dari 5 menit
        if ($user->otp_sent_add && Carbon::parse($user->otp_sent_add)->diffInMinutes(now()) > 5) {
            $user->token_code = '';
            $user->save();

            return response()->json([
                'success'  => false,
                'message' => 'Kode otp sudah kedaluwarsa'
            ], 403);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah atau tidak ditemukan.'
            ], 400);
        }

        // Reset password dari temporary_password
        $user->password = $user->temporary_password;
        $user->temporary_password = null;
        $user->token_code = '';
        $user->verification = true;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset. Silakan login.'
        ]);
    }

    public function validationKodeOTP(Request $request)
    {
        $request ->validate([
            'email' => 'required|email',
            'code_verification' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak ditemukan'
            ], 404);
        }

        // Cek apakah user diblokir sementara
        if ($user->otp_attempts >= 5) {
            $lastAttempt = Carbon::parse($user->last_failed_otp);
            if ($lastAttempt->diffInMinutes(now()) < 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan. Silakan coba lagi dalam 5 menit.'
                ], 429);
            } else {
                // Reset jika sudah lewat 5 menit
                $user->otp_attempts = 0;
                $user->save();
            }
        }
        // hapus token jika lewat dari 5 menit
        if ($user->otp_sent_add && Carbon::parse($user->otp_sent_add)->diffInMinutes(now()) > 5) {
            $user->token_code = '';
            $user->save();

            return response()->json([
                'success'  => false,
                'message' => 'Kode otp sudah kedaluwarsa'
            ], 403);
        }

        // Cek kode OTP
        if ($user->token_code === $request->code_verification) {
            $user->verification = true;
            $user->token_code = '';
            $user->otp_attempts = 0;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP sudah valid.'
            ]);
        }
        // Gagal verifikasi OTP
        $user->otp_attempts += 1;
        $user->last_failed_otp = now();
        $user->save();

        return response()->json([
            'success' => false,
            'message' => 'Kode OTP salah! Percobaan ke-' . $user->otp_attempts
        ], 401);
    }

    public function sendOTP(Request $request) {

        $request->validate([
            'email' =>'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email tidak di temukan'
            ], 404);
        }

        if ($user->otp_sent_add && Carbon::parse($user->otp_sent_add)->diffInSeconds(now()) < 30) {
            return response()->json([
                'success'  => false,
                'message' => 'Tunggu sebentar sebelum meminta OTP lagi.'
            ], 403);
        }

        // generate kode OTP
        $kodeOTP = rand(100000, 999999);


        // simpan token kedalam token kode
        $user->token_code = $kodeOTP;
        $user->otp_type = 'reset';
        \Log::info("Mengirim email ke: " . $user->email);
        $user->otp_sent_add = now();
        $user->save();

        // Kirim email OTP
        try {
            Mail::to($user->email)->send(new VerificationCodeEmail($user, $kodeOTP));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email. Coba lagi nanti.'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode kamu berhasil di kirimkan ke emaail kamu'
        ]);
    }

}

