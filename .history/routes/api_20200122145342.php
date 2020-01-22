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

//Login
Route::post('/login' , 'Api\AuthController@login');


Route::middleware('auth:api')->group(function () {
    //Register
    Route::post('/register' , 'Api\AuthController@register');

    Route::resource('role', 'Api\RoleController');
});
