<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @param Request $request
     * @return array
     * 用户登录的验证方法
     */
    public function loginAuthentic(Request $request){

        // 获取请求发送的所有数据
        $input = $request->all();

        // 获取验证码
        $rules = ['captcha' => 'required|captcha'];     // 定义验证码的验证规则
        $validator = Validator::make(['captcha' => $input['captcha']], $rules);     // 对验证码进行校验
        if ($validator->fails()) {
            return $data=[
                'status'=>'failed',
                'data'=>[false],
                'reason'=>"captcha error"
            ];
        }else {
            $email = $input['email'];
            $password = $input['password'];
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                $userinfo = DB::table('users')
                    ->select('users.id','users.name','users.email','users.phone','users.sex','users.age',
                        'permission_role.permission_id','permissions.name as permissionname','roles.display_name as rolesname')
                    ->join('role_user', 'role_user.user_id', '=', 'users.id')
                    ->join('roles', 'role_user.role_id', '=', 'roles.id')
                    ->join('permission_role', 'role_user.role_id', '=', 'permission_role.role_id')
                    ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
                    ->where('users.email', '=', $email)
                    ->get()->toArray();

                return $data=[
                    'status'=>'success',
                    'data'=>$userinfo,
                    'reason'=>''
                ];
            }else {
                return $data=[
                    'status'=>'fail',
                    'data'=>$input,
                    'reason'=>'email or password error!'
                ];
            }
        }
    }


    /**
     * @return array
     * 注销用户的控制器方法
     */
    public function logout(){
        Auth::logout();
        return $data = [
            'status'=>'success',
            'data'=>[true],
            'reason'=>''
        ];
    }

}
