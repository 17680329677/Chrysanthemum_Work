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
use function GuzzleHttp\Psr7\str;
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
        # 向python部分请求打包和发送邮件的请求
        Helper::sendMessage(json_encode($post_data), $url);
        # 读取redis中python处理的结果
        $key = explode('@', $email);
        $temp = Helper::read_redis($key[0] . '_email', 200, 1);
        $res = json_decode($temp);
        if (isset($res)){
            if ($res->status == 'success'){
                return $data = [
                    'status' => 'success',
                    'reason' => 'pack and send successfully'
                ];
            }else{
                return $data = [
                    'status' => 'failed',
                    'reason' => 'something wrong, please try again!'
                    ];
            }
        }

        return $data = [
            'host' => $url,
            'res' => $key[0]
        ];

    }

}

