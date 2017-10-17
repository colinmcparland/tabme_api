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

Route::post('/user/create', 'ApiController@createUser');

Route::post('/user/validate', 'ApiController@validateUser');

Route::post('/user/cctoken', 'ApiController@addUserCardToken')->middleware('auth:api');

Route::post('/friend/{email}', 'ApiController@addFriend')->middleware('auth:api');

Route::post('/friend/search/{id}', 'ApiController@searchFriends')->middleware('auth:api');

Route::post('/debit', 'ApiController@addDebit')->middleware('auth:api');

Route::get('/debit/{user_id}', 'ApiController@getDebit')->middleware('auth:api');
