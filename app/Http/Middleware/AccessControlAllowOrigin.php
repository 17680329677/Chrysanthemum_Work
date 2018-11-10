<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/12/012
 * Time: 15:24
 */
namespace App\Http\Middleware;

use Closure;

class AccessControlAllowOrigin {

//    public function handle($request, Closure $next){
//        $response = $next($request);
////        $response->header('Access-Control-Allow-Origin', '*');
//        $response->header('Access-Control-Allow-Origin', 'http://127.0.0.1:80');
////        $response->header('Access-Control-Allow-Origin', 'http://172.19.32.120:10082');
//        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, Accept, multipart/form-data, application/json,application/x-www-form-urlencoded, x-csrf-token, x-xsrf-token, Authorization, X-Requested-With, Application');
//        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
//        $response->header('Access-Control-Allow-Credentials', 'true');
//        return $response;
//
//    }

//    public function handle($request, Closure $next)
//    {
//        $response = $next($request);
//        $response->header('Access-Control-Allow-Origin', '*');
//        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, Accept, multipart/form-data, application/json, Authorization, X-Requested-With, Application');
//        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
//        $response->header('Access-Control-Allow-Credentials', 'false');
//        return $response;
//    }

}