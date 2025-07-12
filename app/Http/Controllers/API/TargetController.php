<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Target;
use App\Models\deposit;




class TargetController extends Controller
{

    
    public function index (Request $request) {

    $targets = Target::where('user_id', auth()->id())->get();

        foreach( $targets as $target ) {
            if($target->file){
                $target->file=asset('storage/uploads/' .$target->file);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Daftar target Anda',
            'data' =>$targets
        ]);

    }

    public function store (Request $request)
    {
        // check apakah user sudah pernah buat Target atau belum
        if (Target::where('user_id', auth()->id())->exists()){
            return response()->json([
                'succes' => false,
                'message' => 'Kamu sudah memiliki Target!',
            ], 422);
        };


        $validator = Validator::make($request->all(), [
            'gol'=> 'required|string|distinct',
            'targetAmount' => 'required|integer|min:0',
            'currentAmount' => 'required|integer|min:0',
            'startDate' => 'required|date',
            'endDate'=>  'required|date',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' ,
        ]);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Yah, gagal membuat kategori!',
                'data' => $validator->errors()
            ], 422);
        } 

        // Buat Target
        $input = $request->except('user_id');
        $input['user_id'] = auth()->id();
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time(). '_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads', $filename);
            $input['file'] = $filename;
        }


        $target = Target::create($input);

        return response()->json([
            'success' => true,
            'message' => 'Data Target berhasil di tambahkan',
            'data' => [
                'id' => $target->id,
                'gol' => $target->gol,
                'targetAmount' => $target->targetAmount, 
                'currentAmount' => $target->currentAmount,
                'startDate' => $target->startDate,
                'endDate' => $target->endDate,
                'file' => $target->file ? asset('storage/uploads' . $target->file ) : null
            
            ]
        ]);
    }
    public function update(Request $request)
    {
        $user = auth()->user();
        // Cek autentikasi dulu
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi'
            ], 401);
        }

        // cari Target berdasarkan user yang sedang login 
        $target = Target::where('user_id', auth()->id())->first(); 

        // Mencari Target dari user
        if(!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Anda Belum memiliki target'
            ], 404);
        }
        
        // tampung inputan User
        $validator = Validator::make($request->all(), [
            'gol' => 'sometimes|required|string',
            'targetAmount' => 'sometimes|required|integer',
            'currentAmount' => 'sometimes|required|integer',
            'startDate' => 'sometimes|required|date',
            'endDate' => 'sometimes|required|date',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        // menampilkan error
        if ($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal',
                'data' => $validator->errors()
            ], 422); // Change from 404 to 422
        }

        // Update file jika file ada
        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if ($target->file && Storage::disk('public')->exists('uploads/' . $target->file)) {
                Storage::disk('public')->delete('uploads/' . $target->file);
            }
            
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads', $filename); // Fix: add 'public/'
            $target->file = $filename;
        }

        // Update data lainnya jika data lainnya ada
        \Log::info('Request input untuk update:', $request->all());
        $target->fill($request->except(['user_id', 'file']));
        $target->save();

        // Refresh model untuk memastikan data terbaru
        $target->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Target berhasil diupdate',
            'data' => [
                'id' => $target->id,
                'gol' => $target->gol,
                'targetAmount' => $target->targetAmount, 
                'currentAmount' => $target->currentAmount,
                'startDate' => $target->startDate,
                'endDate' => $target->endDate,
                'file' => $target->file ? asset('storage/uploads/' . $target->file) : null
            ]
        ]);
    }

    public function addprogress(Request $request)
    {
        $target = Target::where('user_id', auth()->id())->first();

        if (!$target) {
            return response()->json([
                'success' => false,
                'message' => 'Target tidak ditemukan',
            ], 404);
        }

        if ($target->currentAmount == $target->targetAmount) {
            return response()->json([
                'success' => true,
                'message' => 'Anda sudah mencapai target yang diinginkan',
                'data' => [
                    'Target Dana' => $target->targetAmount,
                    'Dana Terkumpul' => $target->currentAmount
                ]
            ]);
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
        $input = $request->except('user_id');
        $input['user_id'] = auth()->id();


        // Tambahkan dana dari inputan deposit ke target
        $jumlahSekarang = $target->currentAmount + $request->deposit;
        $target->currentAmount = $jumlahSekarang;
        $target->save();


        $deposit = deposit::create($input);

        return response()->json([
            'success' => true,
            'message' => 'Ini adalah datanya',
            'data' => [
                'id' => $deposit->id,
                'date' => $deposit->date,
                'deposit' => $deposit->deposit
        
            ]
        ]);
    }
}
