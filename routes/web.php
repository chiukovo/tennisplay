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
    return view('index');
});

// SPA Routes - all frontend routes return the same view
// The Vue app handles routing via History API
Route::get('/list', function () {
    return view('index');
});

Route::get('/create', function () {
    return view('index');
});

Route::get('/messages', function () {
    return view('index');
});

Route::get('/auth', function () {
    return view('index');
});
