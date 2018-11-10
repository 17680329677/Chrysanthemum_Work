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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller{
    /*
     * 用户控制器类
     * */

    /**
     * @param Request $request
     * @return array
     * 创建新用户
     */
    public function createUser(Request $request){
        // 获取请求发送的所有数据
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
            $input['password'] = Hash::make($input['password']);
            //$roleid = $input['roles'];
            $user = User::create($input);

            // 给用户赋予角色
//            if($input['roles']) {
//                $user->attachRole($roleid);
//            }
            $user->attachRole(8);

            return $data=[
                'status'=>"success",
                'data'=>[$user],
                'reason'=>""
            ];
        }
    }

    /**
     * @return array
     * 获取当前用户
     */
    public function CurrentUser() {
        $currentUser = Auth::user()['attributes']['email'];
        return $data=[
            'status'=>'success',
            'data'=>[$currentUser],
            'reason'=>""
        ];
    }

    /**
     * @param Request $request
     * @return array
     * 获取当前用户的信息
     */
    public function CurrentUserInfo(Request $request){
        $input = $request->all();
        $email = $input['email'];
        $userInfo = DB::table('users')
            ->select('users.id','users.username','users.name','users.email','users.phone','users.sex','users.age',
                'permission_role.permission_id','permissions.name as permissionname','roles.display_name as rolesname')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->join('permission_role', 'role_user.role_id', '=', 'permission_role.role_id')
            ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
            ->where('users.email', '=', $email)
            ->get()->toArray();

        if ($userInfo){
            return $data=[
                'status'=>'success',
                'data'=>$userInfo,
                'reason'=>""
            ];
        }else{
            return $data=[
                'status'=>'failed',
                'data'=>"",
                'reason'=>'user not found!'
            ];
        }
    }
    

    /**
     * @param Request $request
     * @return array
     * 用户修改信息的控制器方法
     */
    public function updateUserInfo(Request $request){
        $id = $request->get('id');
        $user = User::where('id', $id)->first();
        if ($user){
            $email = $request->get('email');
            if ($email != $user->email){
                $user_check = User::where('email', $email)->first();
                if (!$user_check){
                    $user->email = $email;
                }else{
                    return $data = [
                        'status' => 'failed',
                        'reason' => 'email has already present!'
                    ];
                }
            }
            $user->age = $request->get('age');
            $user->username = $request->get('username');
            $user->name = $request->get('name');
            $user->phone = $request->get('phone');
            $user->sex = $request->get('sex');
            $user->save();
        }
        return $data = [
            'status' => 'success',
            'reason' => 'update success!'
        ];
    }


    /**
     * @param Request $request
     * @return array
     * 用户修改密码的控制器方法
     */
    public function resetpwd(Request $request){
        $input = $request->all();
        $email = $input['email'];
        $old_pwd = $input['old_pwd'];
        $new_pwd = $input['new_pwd'];

        $user = DB::table('users')->where('email', '=', $email)
            ->get()->toArray();
        if (Hash::check($old_pwd, $user[0]->password)){
            $password = Hash::make($new_pwd);
            DB::table('users')->where('email', '=', $email)
                ->update(['password' => $password]);
            return $data = [
                'status'=>'success',
                'reason'=>'reset password success!'
            ];
        }else{
            return $data = [
                'status'=>'failed',
                'reason'=>'old password is not correct!'
            ];
        }
    }

}