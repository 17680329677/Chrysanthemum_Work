<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/11/011
 * Time: 14:24
 */

namespace App\Http\Controllers\User;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller{
    /*
     * 用户控制器类
     * */

    public function createUser(Request $request){

        $input = $request->all();
        // 判断邮箱是否已经被注册
        $info = DB::table('users')->select('id')
            ->where('email', '=', $input['email'])
            ->get()->toArray();
        if ($info) {
            return $data=[
                'status'=>"fail",
                'data'=>"",
                'reason'=>""
            ];
        }
        else {
            $input['password'] = Hash::make(123456);
            //$roleid = $input['roles'];
            $user = User::create($input);

            // 给用户赋予角色
//            if($input['roles']) {
//                $user->attachRole($roleid);
//            }

            return $data=[
                'status'=>"success",
                'data'=>[$user],
                'reason'=>""
            ];
        }
    }
}