<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Target;



class TargetController extends Controller
{
    public function store (Request $request)
    {
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

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time(). '_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads', $filename);
            $input['file'] = $filename;
        }

        // Buat Target
        $input = $request->all();
        $target = Target::create($input);

        return response()->json([
            'success' => true,
            'message' => 'Data Target berhasil di tambahkan',
            'data' => $target,
        ]);
    }
}
