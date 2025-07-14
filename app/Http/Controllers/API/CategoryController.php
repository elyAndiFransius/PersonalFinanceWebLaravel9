<?php

namespace App\Http\Controllers\API;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class CategoryController extends Controller
{
    public function index ()
    {         
        $user = auth()->user();

         // check authentifikasi terlebih dahulu
        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum Login'
            ], 401);
        }

    $categories = Category::whereHas('budget', function($query) use ($user) {
        $query->where('user_id', $user->id);
    })->get(['id', 'name', 'jumlah']);

        return response()->json([
            'success' => true,
            'message' => 'Kategori ditemukan',
            'data' => $categories
        ]);
    }
}
