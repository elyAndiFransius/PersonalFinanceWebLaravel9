<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Target;
use App\Models\deposit;
use Validator;

class DepositController extends Controller
{

    public function index() 
    {
        $deposit = Deposit::where('user_id', auth()->id())->get();

        return response()->json([
            'success' => false,
            'message' => 'Berikut ini adalah datanya',
            'data' => $deposit
        ]);

    }

    public function store(Request $request) 
    {


        $target = Target::where('user_id', auth()->id())->first();

        if ($target->currentAmount >= $target->targetAmount) {
            return response()->json([
                'success' => true,
                'message' => 'Anda sudah melebih target yang diinginkan',
                'data' => [
                    'Target Dana' => $target->targetAmount,
                    'Dana Terkumpul' => $target->currentAmount
                ]
            ]);
        }

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'deposit' => 'required|integer|min:0'
        ]);

        // check authentifikasi terlebih dahulu
        $user = auth()->user();

        if(!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum Login/ Token Expired'
            ], 401);
        }

        // Menampilkan Error
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        \Log::info('Requst untuk masuk', $request->all());
        $validated =  $validator->validated();
        $validated['user_id'] = auth()->id();
 
        // Tambahkan dana dari inputan deposit ke target
        $jumlahSekarang = $target->currentAmount + $request->deposit;
        $target->currentAmount = $jumlahSekarang;

        $target->save();

        $deposit = deposit::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Deposit disimpan',
            'data' => [
                'id' => $deposit->id,
                'date' => $deposit->date,
                'deposit' => $deposit->deposit
        
            ]
        ]);
    }

    public function update(Request $request, Deposit $deposit) 
    {
        $user = auth()->user();
        $target = Target::where('user_id', auth()->id())->first();


        // check authentifikasi
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terdaftar'
            ]);
        }

       $validator = Validator::make($request->all(), [
            'date' => 'sometimes|required|date',
            'deposit' => 'sometimes|required|integer|min:0'
       ]);

       // validasi inputan pengguna
       if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
       }

        \Log::info('Requst untuk masuk', $request->all());
        $validated = $validator->validated();
        $validated['user_id'] = auth()->id();

        $deposit->update($validated);


        // tambahkan dana ke target
        $target->currentAmount += $validated['deposit'];
        $target->save();


        // check terget dan dana sekarang
       if ($target->currentAmount >= $target->targetAmount) {
            return response()->json([
                'success'=> false,
                'message' => 'Anda sudah melebih target yang diinginkan',
                'data' => [
                    'Target Dana' => $target->targetAmount,
                    'Dana Terkumpul' => $target->currentAmount
                ]
            ]);
       }

        return response()->json([
            'success' => true,
            'message' => 'Deposit telah diupdate',
            'data' => $deposit
        ]);

    }

    public function delete(Deposit $deposit) 
    {
        $user = auth()->user();
        $target = Target::where('user_id', auth()->id())->first();

        // check autentifikasi
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terdaftar'
            ], 403);
        }

        // pastikan user memiliki deposit
        if ($deposit->user_id !== $user->id) {
            return  response()->json([
                'success' => false,
                'message' => 'Kamu tidak punya akses untuk deposit ini'  
            ], 404);
        }

        $deposit->delete();

        // tambahkan dana ke target
        $target->currentAmount -=  $deposit->deposit;
        $target->save();

        return response()->json([
            'seccess' => true,
            'message' => 'Data deposit telah di hapus',
            'data' => $deposit
        ]);

    }


}
