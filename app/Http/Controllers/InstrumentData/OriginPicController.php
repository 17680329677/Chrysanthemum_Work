<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/12/012
 * Time: 17:26
 */

namespace App\Http\Controllers\InstrumentData;

use App\Helper;
use App\Http\Controllers\Controller;
use function GuzzleHttp\Psr7\str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class OriginPicController extends Controller{

    protected $host = '127.0.0.1';      // 定义和python通信的ip地址

    static public function array_remove($arr, $offset){
        array_splice($arr, $offset, 1);
    }

    /**
     * @return array
     * 获取所有仪器拍摄的原始图片的信息
     */
    public function getAllOriginPicInfo(){
        try{
//            throw new Exception('something is wrong!');
            $result = DB::table('instrument_origin_pictures')
                ->select('id','instrument_origin_pictures.cultivar_id','id_name.cultivar_name','plant_id','pic_date','angle','revolution_num')
                ->join('id_name', 'instrument_origin_pictures.cultivar_id', '=', 'id_name.cultivar_id')
                ->get()->toArray();
            if ($result){
                return $data = [
                    'status' => 'success',
                    'data' => $result
                ];
            }else {
                return $data = [
                    'status' => 'warning',
                    'reasong' => 'Origin pic is empty!'
                ];
            }
        }catch (Exception $exception){
            return $data = [
                'status' => 'failed',
                'reason' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param Request $request
     * @return array
     * 根据用户选择的筛选信息读取原始图片的信息并返回
     */
    public function getOriginInfoByIndex(Request $request){
        $input = $request->all();
        $IndexList = array();
        $IndexList['cultivar_id'] = $input['cultivar_id'];
        $IndexList['plant_id'] = $input['plant_id'];
        $IndexList['revolution_num'] = $input['revolution_num'];
        $IndexList['pic_date'] = $input['date'];

        try{
            $result = DB::table('instrument_origin_pictures')
                ->select('id','instrument_origin_pictures.cultivar_id','id_name.cultivar_name','plant_id','pic_date','angle','revolution_num')
                ->join('id_name', 'instrument_origin_pictures.cultivar_id', '=', 'id_name.cultivar_id')
                ->where(
                    function ($query) use ($IndexList){
                        foreach ($IndexList as $key => $value){
                            if ($key == 'cultivar_id' or $key == 'plant_id'){
                                if ($IndexList[$key] != null){
                                    $query->where($key, '=', $value);
                                }
                            }elseif ($key == 'pic_date' and $IndexList[$key][0] != null){
                                $query->whereBetween($key, $value);
                            }elseif ($key == 'angle' and $IndexList[$key][0] != null){
                                $query->whereIn($key, $value);
                            }
                        }
                    }
                )->get()->toArray();
            if ($result){
                return $data = [
                    'status' => 'success',
                    'data' => $result
                ];
            }else{
                return $data = [
                    'status' => 'warning',
                    'reason' => 'result is empty!'
                ];
            }
        }catch (Exception $exception){
            return $data = [
                'status' => 'failed',
                'reason' => $exception->getMessage()
            ];
        }
    }

    public function getOriginPic(Request $request){
        $input = $request->all();
        $email = $input['email'];
        $id = $input['id'];
        // 判断用户所选择的图片是否已经经过处理
        $base64_data = array();
        $ids = array();
        foreach ($id as $key => $value){
            $b64 = DB::table('instrument_origin_pictures')->select('base64')
                ->where('id', '=', $value)->get()->toArray();
            if ($b64[0]->base64) {
                $base64_data[$value] = $b64[0]->base64;
                unset($id[$key]);
                $ids = array_values($id);
            }
        }
        // 如果有没有被处理的图片，即数据库中base64为空，则请求
        if ($ids) {
            $post_data = array(
                "email" => $email,
                "id" => $ids
            );
            $emails = explode('@', $email);
            Helper::sendMessage(json_encode($post_data), $this->host . ":4151/pub?topic=instrumentOriginPicProcess");
            $temp = Helper::read_redis('instrument_origin_' . $emails[0], 200, 1);
            $res = json_decode($temp);
            if (isset($res)) {
                if ($res->status == 'success') {
                    foreach ($ids as $value){
                        $b64 = DB::table('instrument_origin_pictures')->select('base64')
                            ->where('id', '=', $value)->get()->toArray();
                        $base64_data[$value] = $b64[0]->base64;
                    }
                    return $data = [
                        'status' => 'process_success!',
                        'data' => $base64_data
                    ];
                }else {
                    return $data = [
                        'status' => 'failed',
                        'reason' => $res->reason
                    ];
                }
            }
        }else {
            return $data = [
                'status' => 'success!',
                'data' => $base64_data
            ];
        }

    }
}