<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/18/018
 * Time: 10:54
 */
namespace App\Http\Controllers\InstrumentData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helper;

class ProcessPicController extends Controller{
    protected $host = '127.0.0.1';

    /**
     * @param Request $request
     * @return array
     * 获取LBP缩略图的控制器方法
     */
    public function getProcessPic(Request $request){
        // 获取用户请求的数据
        $email = $request->get('email');
        $cultivar_id = $request->get('cultivar_id');
        $plant_id = $request->get('plant_id');
        $pic_date = $request->get('pic_date');
        $angle = $request->get('angle');
        $revolution_num = $request->get('revolution_num');

        $result = DB::table('instrument_process_pictures')
            ->where('cultivar_id', '=', $cultivar_id)
            ->where('plant_id', '=', $plant_id)->where('pic_date', '=', $pic_date)
            ->where('angle', '=', $angle)
            ->where('revolution_num', '=', $revolution_num)->get();

        $base64_data = array();
        $pathList = array();
        // 如果查询结果不为空且图片已经进行了处理
        if ($result and $result[0]->base64){
            for ($i = 0; $i < count($result); $i++){
                $base64_data[$result[$i]->process_type] = $result[$i]->base64;
            }
            return $data = [
                'status' => 'success',
                'pic' => $base64_data
            ];
        }elseif ($result) {     // 若未经过处理，则请求python进行处理
            for ($i = 0; $i < count($result); $i++){
                array_push($pathList, $result[$i]->path);
            }
            $post_data = array(
                'email' => $email,
                'pathList' => $pathList
            );
            $email_key = explode('@', $email);
            Helper::sendMessage(json_encode($post_data), $this->host . ":4151/pub?topic=instrumentProPicProcess");
            $temp = Helper::read_redis($email_key[0] . '_pro_process', 200, 1);
            $res = json_decode($temp);
            if (isset($res) and $res->status == 'success'){
                $result = DB::table('instrument_process_pictures')
                    ->where('cultivar_id', '=', $cultivar_id)
                    ->where('plant_id', '=', $plant_id)->where('pic_date', '=', $pic_date)
                    ->where('angle', '=', $angle)
                    ->where('revolution_num', '=', $revolution_num)->get();
                for ($i = 0; $i < count($result); $i++){
                    $base64_data[$result[$i]->process_type] = $result[$i]->base64;
                }
                return $data = [
                    'status' => 'success',
                    'pic' => $base64_data
                ];
            }else{
                return $data = [
                    'status' => 'failed',
                    'reason' => $res->reason
                ];
            }
        }

    }
}