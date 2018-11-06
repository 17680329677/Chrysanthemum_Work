<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
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
        $this->middleware('guest');
    }

    public function resetPassword(Request $request){
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
