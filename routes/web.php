<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/




Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/test', 'CommonController@test')->name('test');
Route::get('/migrationForCustomerMaster', 'CommonController@migrationForCustomerMaster')->name('customer_master');
Route::get('/migrationForDeliveryConfigMaster', 'CommonController@migrationForDeliveryConfigMaster')->name('delivery_config');
Route::get('/migrationForGarmentMaster', 'CommonController@migrationForGarmentMaster')->name('garment_master');
Route::get('/migrationForHub', 'CommonController@migrationForHub')->name('hub');


