<?php

use Illuminate\Http\Request;

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
Route::group(['namespace' => 'Api'], function () {
    Route::resource('categories', 'CategoryController',['except'=> ['create', 'edit']]);
    Route::resource('genres', 'GenreController',['except'=> ['create', 'edit']]);
    Route::resource('cast_members', 'CastMemberController',['except'=> ['create', 'edit']]); //para melhor codigo, quando trabalhar com nome composto usar _ cast_member
    Route::resource('videos', 'VideoController', ['except' => ['create', 'edit']]);
});

