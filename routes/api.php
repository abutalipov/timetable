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

Route::group([
    'middleware' => ['api'],
], function () {

    Route::post('login/request', 'AuthController@smsCodeRequest');
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'AuthController@logout');

        Route::resource('occupation',OccupationController::class);
        Route::resource('location',LocationController::class);
        Route::resource('group',GroupController::class);
        Route::resource('timetable',TimetableController::class);
        Route::resource('user',UserController::class);

    });
});
