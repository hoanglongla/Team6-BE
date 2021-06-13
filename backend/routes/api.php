<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("/start/success", function (){
    return \ApiService::success("ok");
});

Route::get("/start/fail", function (){
    return \ApiService::fail("Ban can dang nhap", 1, []);
});


Route::get("/login", [\App\Http\Controllers\Api\LoginController::class,'login']);

