<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Validator;

class BudgetController extends Controller
{
    public function index()
    {
        // Untuk Mengambil budget data
        $budget = Budget::with('categories')
            ->where('user_id', auth()->id())
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Berikut ini adalah data Budget',
            'data' => $budget
        ]);
    }

    public function create(Request $request)
    {
        // Check apakah user sudah pernah buat budget atau bekum
        if(Budget::where('user_id', auth()->id())->exists()){
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah memiliki Budget!',
            ], 422);
        };

        // Ambil inputan user 
        $validator = Validator::make($request->all(), [
            'pemasukkan' => 'required|integer|min:0',
            'priode' => 'required|in:Harian,Mingguan,Bulanan,Tahunan',
            'categories' => 'required|array|min:1',
            'categories.*.name' => 'required|string|distinct',
            'categories.*.jumlah' => 'required|integer|min:0',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Yah gagal, kategori kosong!',
                'data' => $validator->errors()
            ], 422);
        }

        // Buat budget
        $budget = Budget::create([
            'user_id' => auth()->id(),
            'pemasukkan' => $request->pemasukkan,
            'priode' => $request->priode
        ]);

        // Buat kategori-kategori yang terhubung ke budget ini
        foreach ($request->categories as $category)
        {
            $budget->Categories()->create([
                'name' => $category['name'],
                'jumlah' => $category['jumlah'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mantap! Data budget dan kategori berhasil dibuat!',
            'data' => $budget->load('categories') // jika kamu buat relasi di model
        ]);
    }

    public function update(Request $request, Budget $budget)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentifikasi'
            ], 401);
        }

        // Pastikan user milik budget yang dengan login
        if ($budget->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak punya akses ke budget ini.'
            ], 403);
        }

        // Validasi
        $validator = Validator::make($request->all(), [
            'pemasukkan' => 'sometimes|required|integer|min:0',
            'priode' => 'sometimes|required|in:Harian,Mingguan,Bulanan,Tahunan',
            'categories' => 'sometimes|required|array|min:1',
            'categories.*.name' => 'sometimes|required|string|distinct',
            'categories.*.jumlah' => 'sometimes|required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors()
            ], 422);
        }

        // Update fields
        if ($request->has('pemasukkan')) {
            $budget->pemasukkan = $request->pemasukkan;
        }

        if ($request->has('priode')) {
            $budget->priode = $request->priode;
        }

        $budget->save();

        if ($request->has('categories')) {
            $totalKategori = collect($request->categories)->sum('jumlah');

            // Gunakan pemasukkan dari request jika ada, atau dari budget lama
            $pemasukkan = $request->has('pemasukkan') ? $request->pemasukkan : $budget->pemasukkan;

            if ($totalKategori > $pemasukkan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total jumlah kategori tidak boleh melebihi pemasukkan.',
                    'data' => [
                        'total_kategori' => $totalKategori,
                        'pemasukkan' => $pemasukkan
                    ]
                ], 422);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Budget telah berhasil diupdate',
            'data' => $budget->load('categories')
        ]);
    }

    public function destroy(Budget $budget)
    {
      
        $user = auth()->user();

        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terdaftar'
            ], 401);
        }
        // Pastikan user memiliki budget yang sedang login
        if ($budget->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak punya akses ke budget Ini.'
            ], 403);
        }

        $budget->delete();
         
        return response()->json([
            'success' => true,
            'message' => 'Data dudgeting kamu berhasil di hapus'
        ]);
    }


}
