<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Target;

class GrafikController extends Controller
{
    public function indexKategori(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login'
            ]);
        }

        $budget = $user->budget()->with('categories')->first();

        if (!$budget) {
            return response()->json([]);
        }

        $pemasukkan = $budget->pemasukkan;
        $priode = $budget->priode;

        // Ambil data kategori + jumlah + budget->pemasukkan
        $data = $budget->categories->map(function ($kategori) use ($pemasukkan, $priode) {
            return [
                'kategori' => $kategori->name,
                'jumlah' => $kategori->jumlah,
                'pemasukkan' => $pemasukkan,
                'priode' => $priode

            ];
        });

        return response()->json($data);


    }
    public function indexTarget(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum login'
            ]);
        }

        $targets = Target::where('user_id', auth()->id())->get()->first();

        if (!$targets) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak di temukan'
            ], 200);
        }


        // Ambil data kategori + jumlah + budget->pemasukkan
        $data = [
                'gol' => $targets->gol,
                'targetAmount' => $targets->targetAmount,
                'currentAmount' => $targets->currentAmount
            ];


        return response()->json($data);

    }
}
