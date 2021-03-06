<?php

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

// 获取搜索结果
Route::get('/search', "Api\SearchController@search")->name("api.search");
Route::post('/message', "Api\SearchController@message")->name("api.message");
Route::post('/friend', "Api\SearchController@friend")->name("api.friend");
Route::get('/suggest', "Api\SearchController@suggest")->name("api.suggest");
