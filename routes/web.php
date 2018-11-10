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
Route::get('/addrole', 'User\RolesController@createRole');      // 添加角色

Route::post('/login', 'Auth\LoginController@loginAuthentic');       // 用户登录验证

Route::post('/logout', 'Auth\LoginController@logout');      // 注销用户

Route::post('/adduser', 'User\UserController@createUser');      // 添加新用户

Route::get('/currentuser', 'User\UserController@CurrentUser');      // 获取当前用户

Route::post('currentuserinfo', 'User\UserController@CurrentUserInfo');  // 获取当前用户信息

Route::post('/resetpassword', 'User\UserController@resetpwd'); // 修改用户密码

Route::post('/updateUserInfo', 'User\UserController@updateUserInfo');

// 人工数据路由
Route::post('/allartificial', 'ArtificialData\ArtificialController@getAll');       // 获取所有人工拍摄的性状数据

Route::post('/getcharacterbyname', 'ArtificialData\ArtificialController@getCharacterByName');       // 根据品种名模糊检索人工数据

Route::post('/getcharacterbyindex', 'ArtificialData\ArtificialController@getCharacterByIndex');     // 根据索引项检索数据

Route::post('/picprocess', 'ArtificialData\ArtificialController@picProcess');   // 图片处理的路由 将图片压缩并转换为base64

Route::post('/sendemail', 'Email\EmailController@sendEmail');

//Route::get('/download', function (){
//    return response()->download('G:/Chrysanthemum_Work/public/test.txt', '18510363933.txt');
//});
Route::get('/download', 'Email\DownLoadController@download');