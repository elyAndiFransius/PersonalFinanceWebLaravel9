<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BudgetController;
use App\Http\Controllers\API\TargetController;
use App\Http\Controllers\API\TrasaksiController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\GrafikController;
use App\Http\Controllers\API\DepositController;


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

// Target Routing
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/targets/store', [TargetController::class, 'store']);
    Route::get('/targets', [TargetController::class, 'index']); // di mobile juga belum di bisa
    Route::put('/targets/update', [TargetController::class, 'update']); // di mobile belum bisa
    Route::post('/targets/addprogress', [TargetController::class, 'addprogress']); // di mobile belum bisa 
    Route::delete('/targets/destory/{target}', [TargetController::class, 'destory']);

});

// Budget Routing
Route::middleware('auth:sanctum')->group(function (){
    Route::post('/budgets/create', [BudgetController::class, 'create']);
    Route::get('/budgets', [BudgetController::class, 'index']);
    Route::put('/budgets/update/{budget}', [BudgetController::class, 'update']); // di mobile belum bisa 
    Route::delete('/budgets/delete/{budget}', [BudgetController::class, 'destroy']);
});


Route::middleware('auth:sanctum')->group(function (){
    Route::get('/transaksi', [TrasaksiController::class, 'index']);
    Route::post('/transaksi/store', [TrasaksiController::class, 'store']);
    Route::put('/transaksi/update/{transaksi}', [TrasaksiController::class, 'update']);
    Route::delete('/transaksi/delete/{transaksi}', [TrasaksiController::class, 'destroy']);

});

Route::middleware('auth:sanctum')->group(function (){
    Route::get('/categories', [CategoryController::class, 'index']);
});


// Deposit
Route::middleware('auth:sanctum')->group(function (){
    Route::get('/deposit', [DepositController::class, 'index']);
    Route::post('/deposit/store', [DepositController::class, 'store']);
    Route::put('/deposit/update/{deposit}', [DepositController::class, 'update']);
    Route::delete('/deposit/delete/{deposit}', [DepositController::class, 'delete']);
});

// Grafik
Route::middleware('auth:sanctum')->group(function (){
    Route::get('/grafik', [GrafikController::class, 'index']);
    
});
