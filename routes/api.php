<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BudgetController;
use App\Http\Controllers\API\TargetController;
use App\Http\Controllers\API\TrasaksiController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Mengarahkan register Path AuthController
Route::post('register', [AuthController::class, 'register']);

Route::post('/validationKodeOTP', [AuthController::class, 'validationKodeOTP']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/profile', function (Request $request) {
    return response()->json([
        'success' => true,
        'user' => $request->user()
    ]);
});


Route::middleware('auth:sanctum')->group(function (){
    Route::post('/targets/store', [TargetController::class, 'store']);
    Route::get('/targets', [TargetController::class, 'index']); // di mobile juga belum di bisa
    Route::put('/targets/update', [TargetController::class, 'update']); // di mobile belum bisa
});


Route::middleware('auth:sanctum')->group(function (){
    Route::post('/budgets', [BudgetController::class, 'create']);
});


Route::middleware('auth:sanctum')->group(function (){
        Route::get('/transaksi/index', [TrasaksiController::class, 'index']);
    Route::post('/transaksi', [TrasaksiController::class, 'store']);

});
