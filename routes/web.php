<?php

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
    return view('welcome');
});
//测试页面
Route::get('/test', function () {
    return view('helptest/testhelp/index');
});
//分享
Route::get('/ok', function () {
    return view('share/index');
});
