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

// 用户部分路由
Route::get('/addrole', 'User\RolesController@createRole');

Route::post('/login', 'Auth\LoginController@loginAuthentic');

Route::post('/logout', 'Auth\LoginController@logout');

Route::post('/adduser', 'User\UserController@createUser');

// 人工数据路由
Route::post('/allartificial', 'ArtificialData\ArtificialController@getAll');       // 获取所有人工拍摄的性状数据

Route::post('/getcharacterbyname', 'ArtificialData\ArtificialController@getCharacterByName');       // 根据品种名模糊检索人工数据

Route::post('/getcharacterbyindex', 'ArtificialData\ArtificialController@getCharacterByIndex');     // 根据索引项检索数据