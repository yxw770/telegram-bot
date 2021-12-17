<?php

//use App\Http\Controllers\UserController;
//use App\Http\Controllers\Api\v1\;
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
    return view('welcome');
});
Route::prefix('/api/v1')->group(function() {
    Route::post('notify/{type}/{token}',[\App\Http\Controllers\Api\V1\IndexController::class,'notify']);
});
Route::prefix('/api/v1/admin')->group(function() {
    Route::post('log/in',[\App\Http\Controllers\Api\V1\Admin\LoginController::class,'login']);


    Route::post('command/add',[\App\Http\Controllers\Api\V1\Admin\CommandController::class,'add']);
    Route::put('vip/update',[\App\Http\Controllers\Api\V1\Admin\VipController::class,'updateGroup']);
});


