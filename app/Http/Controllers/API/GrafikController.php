<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GrafikController extends Controller
{
    public function index(Request $request)
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
            return response()->json([
                'success' => false,
                'message' => 'Budget tidak di temukan'
            ], 404);
        }

        $pemasukkan = $budget->pemasukkan;

        // Ambil data kategori + jumlah + budget->pemasukkan
        $data = $budget->categories->map(function ($kategori) use ($pemasukkan) {
            return [
                'kategori' => $kategori->name,
                'jumlah' => $kategori->jumlah,
                'pemasukkan' => $pemasukkan
            ];
        });

        return response()->json($data);


    }
}
