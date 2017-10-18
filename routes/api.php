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

Route::post('/user/create', 'UserController@createUser');

Route::post('/user/validate', 'UserController@validateUser');

Route::post('/user/cctoken', 'UserController@addUserCardToken')->middleware('auth:api');

Route::post('/friend/{email}', 'FriendController@addFriend')->middleware('auth:api');

Route::post('/friend/search/{id}', 'FriendController@searchFriends')->middleware('auth:api');

Route::post('/debit', 'DebitsController@addDebit')->middleware('auth:api');

Route::get('/debit/{user_id}', 'DebitsController@getDebit')->middleware('auth:api');

Route::get('/debit/owing/{user_id}', 'DebitsController@getDebitOwing')->middleware('auth:api');

Route::post('/debit/payback', 'DebitsController@payBackDebit')->middleware('auth:api');

