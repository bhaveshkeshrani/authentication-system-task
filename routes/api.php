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
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // For Testing purpose
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('update/profile', 'AuthController@updateUserProfile')->name('auth.updateUserProfile');
});

Route::prefix('v1')
    ->group(function () {
    });
Route::prefix('v1')
    ->group(function () {
    Route::post('invite/user', 'AuthController@invite')->name('auth.invite');
    Route::post('register', 'AuthController@store')->name('auth.register');
    Route::post('verify/pin', 'AuthController@verifyPin')->name('auth.VerifyPin');
    Route::post('login', 'AuthController@login')->name('auth.login');
});