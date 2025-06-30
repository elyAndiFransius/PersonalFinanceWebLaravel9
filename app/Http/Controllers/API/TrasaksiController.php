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

        // Check Dulu 
        if($request->jenis === 'pengeluaran') {
            $category = Category::where('id', $request->categories_id)->first();

            if(!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category untuk pengeluaran tidak di temukan' 
                ], 404);
            }

            $totalPengeluaran = Transaksi::where('categories_id', $request->categories_id)
                ->where('jenis', 'pengeluaran')
                ->sum('jumlah');

                $sisa = $category->jumlah - $totalPengeluaran;

            if ($request->jumlah > $sisa) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jumlah pengeluaran melebihi sisa budget. Sisa: ' . $sisa
                ], 400);
            }
        }

        $transaksi = Transaksi::create($input);
        // Kurangi saldo category jika pengeluaran
        if ($request->jenis === 'pengeluaran') {
            $category->jumlah -= $request->jumlah;
            $category->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Data Transaksi Berhasil ditambahkan',
            'data' => [
                'id' => $transaksi->id,
                'jenis' => $transaksi->jenis,
                'descripsi' => $transaksi->descripsi,
                'jumlah' => $transaksi->jumlah,
                'date' =>  $transaksi->date,
            ]
        ]);

    }

    public function show(Transaksi $transaksi)
    {
        //
    }


    public function edit(Transaksi $transaksi)
    {
        //
    }


    public function update(Request $request, Transaksi $transaksi)
    {
        //
    }

    public function destroy(Transaksi $transaksi)
    {
        //
    }
}
