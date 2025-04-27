<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use Illuminate\Support\Facades\Auth;



class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);

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
        $user = User::create($input); // Menyimpan semua data yang di inputkan melalui variabel $input
 
        $success['token'] = $user->createToken('auth_token')->plainTextToken; //Membuat dan menampilkan token authtifikasi
        $success['name'] = $user->name; // menampilkan name

        // Jika berhasil menmapilkan data token dan namenya
        return response()->json([
            'success' => true,
            'message' => 'Berhasil Membuat akun',
            'data' => $success,
        ]);
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
}

