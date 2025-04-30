<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
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
            'email' => 'required|email|unique:'. User::class,
            'password' => 'required',
            'confirm_password' => 'required|same:password'
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
        $input['token_code'] = $code_verification; 
        $user = User::create($input); // Menyimpan semua data yang di inputkan melalui variabel $input


       // Mail::to($user -> $email)->send(new VerificationCodeEmail($user));

 
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
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Coba login
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Login gagal, email atau password salah.'
            ], 401);
        }

        // Jika sukses login
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function validationKodeOTP(Request $request)
    {
        $request ->validate([
            'email' => 'required|email',
            'code_verification' => 'required'
        ]);

        $user = User::where('email', $request->email)
                    ->where('token_code', $request->code_verification)
                    ->first();

        if($user)
        {
            $user->verification=true;
            $user->token_code='';
            $user->save();

            return response()->json([
                'success' => True,
                'message' => 'Kode OTP sudah Nice',

            ]);
            return redirect()->route('login')->with('success', 'Verifikasi telah berhasil. Silahkan Login!');
        }

       // return back()->withErrors(['code' => 'Kode OTP salah!']) -> withInput();

       return response()->json([
        'success' => False,
        'message' => 'Kode OTP belum Nice',

    ], 404);
        
    }

}

