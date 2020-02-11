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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('login',                'API\UsersController@login');
Route::post('registerClient',       'API\UsersController@registerClient');
Route::post('registerContractor',   'API\UsersController@registerContractor');

Route::group(['middleware' => 'auth:api'], function(){
    Route::post('details',          'API\UsersController@details');

    Route::get('menu',              'API\MenusController@index');

    Route::get('meals',             'API\MealsController@index');
    Route::post('meals',            'API\MealsController@store');
    Route::post('meals/{meal}',     'API\MealsController@update');
    Route::delete('meals/{meal}',   'API\MealsController@destroy')->name('admin/vub-codes/destroy');
});
