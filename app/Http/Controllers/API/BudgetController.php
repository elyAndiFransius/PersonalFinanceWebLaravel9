<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Validator;

class BudgetController extends Controller
{
    public function index(Request $request)
    {

    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pemasukkan' => 'required|integer|min:0',
            'priode' => 'required|in:harian,mingguan,bulanan,tahunan',
            'categories' => 'required|array|min:1',
            'categories.*.name' => 'required|string|distinct',
            'categories.*.jumlah' => 'required|integer|min:0',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Yah, gagal membuat kategori!',
                'data' => $validator->errors()
            ], 422);
        }

        // Buat budget
        $budget = Budget::create([
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


    public function show(Budget $budget)
    {
        //
    }


    public function edit(Budget $budget)
    {
        //
    }


    public function update(Request $request, Budget $budget)
    {
        //
    }

    public function destroy(Budget $budget)
    {
        //
    }
}
