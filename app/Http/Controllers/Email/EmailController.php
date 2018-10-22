<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/22/022
 * Time: 9:47
 * Func: 发送邮件的控制器
 */
namespace App\Http\Controllers\Email;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helper;


class EmailController extends Controller {
    protected $host = '127.0.0.1';

    public function sendEmail(Request $request) {
        $input = $request->all();
        $email = $input['email'];
        $classification = $input['classification'];
        $cultivar_id = $input['id'];
        $qualiy = $input['quality'];

        $post_data = array(
            'email' => $email,
            'classification' => $classification,
            'cultivar_id' => $cultivar_id,
            'quality' => $qualiy
        );
        $url = $this->host . ":4151/pub?topic=sendEmail";
        Helper::sendMessage(json_encode($post_data), $url);
        return $data = [
            'host' => $url
        ];

    }

}

