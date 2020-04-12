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


Route::post('login',                'API\UsersController@login');
Route::post('registerClient',       'API\UsersController@registerClient');
Route::post('registerContractor',   'API\UsersController@registerContractor');

Route::group(['middleware' => 'auth:api'], function() {
    Route::post('logout',                                       'API\UsersController@logout');
    Route::get('details',                                       'API\UsersController@details');

    // Client employee
    Route::get('employees',                                     'API\EmployeesController@index');
    Route::post('employees',                                    'API\EmployeesController@register');
    Route::get('employees/{employee}',                          'API\EmployeesController@show');
    Route::put('employees/{employee}',                          'API\EmployeesController@update');
    Route::delete('employees/{employee}',                       'API\EmployeesController@destroy');

//    Route::get('menu',                                          'API\MenusController@index');

    // Meals
    Route::get('meals',                                         'API\MealsController@index');
    Route::post('meals',                                        'API\MealsController@store');
    Route::get('meals/{meal}',                                  'API\MealsController@show');
    Route::put('meals/{meal}',                                  'API\MealsController@update');
    Route::delete('meals/{meal}',                               'API\MealsController@destroy');
    
    // Agreements
    Route::get('agreements',                                    'API\AgreementsController@index');
    Route::post('agreements',                                   'API\AgreementsController@create');
    Route::post('agreements/{agreement}/confirm',               'API\AgreementsController@confirm');

    // Restaurants
    Route::get('restaurants',                                   'API\RestaurantsController@index');

    // Orders
    Route::get('orders/employee',                               'API\OrdersController@employee');
    Route::get('orders/{type?}',                                'API\OrdersController@index');
    Route::post('orders',                                       'API\OrdersController@store');

    // Type of employments
    Route::get('type-of-employments',                          'API\TypeOfEmploymentsController@index');
    Route::post('type-of-employments',                         'API\TypeOfEmploymentsController@store');
    Route::get('type-of-employments/{typeOfEmployment}',       'API\TypeOfEmploymentsController@show');
    Route::put('type-of-employments/{typeOfEmployment}',       'API\TypeOfEmploymentsController@update');
    Route::delete('type-of-employments/{typeOfEmployment}',    'API\TypeOfEmploymentsController@destroy');
});
