<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('guest/home'); 
});

Route::get('/about', function () {
    return view('guest/about');
});

Route::get('/contact', function () {
    return view('guest/contact');
});

Route::get('/download', function () {
    return view('guest/comingsoon');
});