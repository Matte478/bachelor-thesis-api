<?php

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

Route::group(['middleware' => 'auth:api'], function() {
    Route::post('logout',                                       'API\UsersController@logout');
    Route::post('details',                                      'API\UsersController@details');

    // Client employee
    Route::get('employees',                                     'API\UsersController@employees');
    Route::post('employees',                                    'API\UsersController@registerClientEmployee');
    Route::get('employees/{employee}',                          'API\UsersController@getClientEmployee');

    Route::get('menu',                                          'API\MenusController@index');

    // Meals
    Route::get('meals',                                         'API\MealsController@index');
    Route::post('meals',                                        'API\MealsController@store');
    Route::get('meals/{meal}',                                  'API\MealsController@show');
    Route::post('meals/{meal}',                                 'API\MealsController@update');
    Route::delete('meals/{meal}',                               'API\MealsController@destroy');
    
    // Agreements
    Route::get('agreements',                                    'API\AgreementController@index');
    Route::post('agreements',                                   'API\AgreementController@create');
    Route::post('agreements/{agreement}/confirm',               'API\AgreementController@confirm');

    // Restaurants
    Route::get('restaurants',                                   'API\RestaurantController@index');

    // Orders
    Route::get('orders/{type?}',                                'API\OrderController@index');
    Route::post('orders',                                       'API\OrderController@store');

    // Type of employments
    Route::get('type-of-employments',                          'API\TypeOfEmploymentsController@index');
    Route::post('type-of-employments',                         'API\TypeOfEmploymentsController@store');
    Route::get('type-of-employments/{typeOfEmployment}',       'API\TypeOfEmploymentsController@show');
    Route::post('type-of-employments/{typeOfEmployment}',      'API\TypeOfEmploymentsController@update');
    Route::delete('type-of-employments/{typeOfEmployment}',    'API\TypeOfEmploymentsController@destroy');
});
