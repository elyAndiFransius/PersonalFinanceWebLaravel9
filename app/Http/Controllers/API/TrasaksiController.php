<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\Category;
use Validator;
use Illuminate\Http\Request;


class TrasaksiController extends Controller
{

    public function index()
    {
       
        $transaksi = Transaksi::where('user_id', auth()->id())->get();

        return response()->json([
            'success' => true,
            'message' =>'Berikut ini adalah datanya',
            'data' => $transaksi
        ]);

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'categories_id' => 'required|exists:categories,id',
            'jenis' => 'required|in:pemasukkan,pengeluaran',
            'descripsi' => 'required|string',
            'jumlah' => 'required|numeric|min:1',
            'date' =>  'required|string'
         ]);

         // check authentifikasi terlebih dahulu
         $user = auth()->user();

         if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum Login'
            ], 401);
         }

         // Menampilkan error
         if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'data' => $validator->errors()
            ], 422);
         }
         \Log::info('Request Untuk masuk', $request->all());
         $input = $request->except('user_id');
         $input['user_id'] = auth()->id();


        $category = Category::where('id', $request->categories_id)
            ->whereHas('budget', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        // Jika jenisnya pengeluaran, lakukan pengecekan sisa budget
        if ($request->jenis === 'pengeluaran') {
            $totalPengeluaran = Transaksi::where('categories_id', $request->categories_id)
                ->where('jenis', 'pengeluaran')
                ->sum('jumlah');

                $sisa = $category->jumlah - $totalPengeluaran;

                if ($request->jumlah > $sisa) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jumlah pengeluaran melebihi sisa budget. Sisa: ' . $sisa
                    ], 400);
                }

            // Kurangi saldo category
            $category->jumlah -= $request->jumlah;
            }
            // Jika jenisnya pemasukkan, langsung tambahkan ke saldo category
            elseif ($request->jenis === 'pemasukkan') {
                $category->jumlah += $request->jumlah;
            }

            // Simpan perubahan saldo category
            $category->save();

            // update jumlah budget setelah kategori di ubah
            $budget = $category->budget;

            $totalKategori  = $budget->categories()->sum('jumlah');

            $budget->pemasukkan =  $totalKategori;

            $budget->save();


            // Simpan transaksi
            $transaksi = Transaksi::create($input);

            return response()->json([
                'success' => true,
                'message' => 'Data Transaksi berhasil ditambahkan.',
                'data' => [
                    'id' => $transaksi->id,
                    'jenis' => $transaksi->jenis,
                    'descripsi' => $transaksi->descripsi,
                    'jumlah' => $transaksi->jumlah,
                    'date' => $transaksi->date,
                ]
            ]);
        
    
        }
    
    public function update(Request $request, Transaksi $transaksi)
    {
        $user = auth()->user();

        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentifikasi'
            ]);
        }

        if ($transaksi->user_id !== $user->id) {
            return response()->json([
            'success' => false,
            'message' => 'Kamu tidak punya akses ke transaksi ini'
            ], 403);
        }

        // validasi
        $validator = Validator::make($request->all(), [
            'categories_id' => 'sometimes|required|exists:categories,id',
            'jenis' => 'sometimes|required|in:pemasukkan,pengeluaran',
            'descripsi' => 'sometimes|required|string',
            'jumlah' => 'sometimes|required|numeric|min:1',
            'date' =>  'sometimes|required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'validasi gagal,',
                'data' => $validator->errors()
            ],);
        }


        $validated = $validator->validated();
        $validated['user_id'] = auth()->id();

        $category = Category::where('id', $request->categories_id)
            ->whereHas('budget', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();


        // Jika jenisnya pengeluaran, lakukan pengecekan sisa budget
        if ($request->jenis === 'pengeluaran') {
            $totalPengeluaran = Transaksi::where('categories_id', $request->categories_id)
                ->where('jenis', 'pengeluaran')
                ->sum('jumlah');

                $sisa = $category->jumlah - $totalPengeluaran;

                if ($request->jumlah > $sisa) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Jumlah pengeluaran melebihi sisa budget. Sisa: ' . $sisa
                        ], 400);
                    }
                    // Kurangi saldo category
                    $category->jumlah -= $request->jumlah;
                    }
                // Jika jenisnya pemasukkan, langsung tambahkan ke saldo category
                elseif ($request->jenis === 'pemasukkan') {
                    $category->jumlah += $request->jumlah;
                }

        // Simpan perubahan saldo category
        $category->save();

        // update jumlah budget setelah kategori di ubah
        $budget = $category->budget;

        $totalKategori  = $budget->categories()->sum('jumlah');

        $budget->pemasukkan =  $totalKategori;

        $budget->save();
        
        $transaksi->update($validated);

        return response()->json([
            'success' => false,
            'message' => 'Data transaksi berhasil di ubah',
            'data' =>  $transaksi
        ]);

    }

        
    public function destroy(Transaksi $transaksi)
    {
        $user = auth()->user();

        // untuk user tidak terdatar
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terdaftar'
            ], 401);
        }

        // pastikan user memilki target yang sedang login
        if ($transaksi->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu tidak punya akses untuk target ini.'
            ], 403);
        }

        $category = Category::where('id', $transaksi->categories_id)
            ->whereHas('budget', function($query) use ($user) {
                $query->where('user_id', $user->id);
        })->first();

        // Simpan perubahan saldo category
        $category->save();

        $budget = $category->budget;

        $totalKategori  = $budget->categories()->sum('jumlah');

        $budget->pemasukkan =  $totalKategori;

        $budget->save();
        
        $transaksi->delete();

        return response()->json([
            'succes' => true,
            'message' => 'Data target telah di hapus',
            'data' => $transaksi
        ]);
    }



}
