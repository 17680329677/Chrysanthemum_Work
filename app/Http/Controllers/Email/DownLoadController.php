<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/5/005
 * Time: 17:51
 */
namespace App\Http\Controllers\Email;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class DownLoadController extends Controller{
    public function download(Request $request){
        $headers = array(
            'Content-Type: application/zip',
        );
        $email = $request->get('email');
        $email_key = explode('@', $email);
        $file = 'G:\\mum_users_file\\' . $email_key[0] . '\\' . $email_key[0] . '.zip';
        return response()->download($file, 'data.zip', $headers);
    }
}