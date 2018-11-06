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
use App\Http\Controllers\Email\DownLoadController;


class EmailController extends Controller {
    protected $host = '127.0.0.1';

    public function sendEmail(Request $request) {
        $input = $request->all();
        $email = $input['email'];
        $classification = $input['classification'];
        $cultivar_id = $input['id'];
        $qualiy = $input['quality'];
        $type = $input['type'];

        $post_data = array(
            'email' => $email,
            'classification' => $classification,
            'cultivar_id' => $cultivar_id,
            'quality' => $qualiy,
            'type' => $type
        );
        $url = $this->host . ":4151/pub?topic=sendEmail";
        # 向python部分请求打包和发送邮件的请求
        Helper::sendMessage(json_encode($post_data), $url);
        # 读取redis中python处理的结果
        $key = explode('@', $email);
        if ($type == 'mail'){
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
        }else if ($type == 'download'){
            $temp = Helper::read_redis($key[0] . '_dnload', 200, 1);
            $res = json_decode($temp);
            if ($res->status == 'success'){
                return $data = [
                    'status' => 'success',
                    'reason' => 'files are ready to download!'
                ];
//                return redirect()->action('Email\DownLoadController@download');
            }
        }

        return $data = [
            'host' => $url,
            'res' => $key[0]
        ];

    }

}

