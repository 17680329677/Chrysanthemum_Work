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

    protected $host = '127.0.0.1';      // �����pythonͨ�ŵ�ip��ַ

    static public function array_remove($arr, $offset){
        array_splice($arr, $offset, 1);
    }

    /**
     * @return array
     * ��ȡ�������������ԭʼͼƬ����Ϣ
     */
    public function getAllOriginPicInfo(){
        try{
//            throw new Exception('something is wrong!');
            // ��ѯԭʼͼƬ����Ϣ
            $result = DB::table('instrument_origin_pictures')
                ->select('id','instrument_origin_pictures.cultivar_id','id_name.cultivar_name','plant_id','pic_date','angle','revolution_num')
                ->join('id_name', 'instrument_origin_pictures.cultivar_id', '=', 'id_name.cultivar_id')
                ->get()->toArray();

            if ($result){
                // ����ѯ�����Ϊ�գ������ԭʼͼƬ��Ϣ�жϸ�ͼƬ�Ƿ��ж�Ӧ��LBPͼƬ����ֵ����
                for ($i = 0; $i < count($result); $i++){
                    $res_pic = DB::table('instrument_process_pictures')
                        ->where('cultivar_id', '=', $result[$i]->cultivar_id)
                        ->where('plant_id', '=', $result[$i]->plant_id)->where('pic_date', '=', $result[$i]->pic_date)
                        ->where('angle', '=', $result[$i]->angle)
                        ->where('revolution_num', '=', $result[$i]->revolution_num)->get();
                    $result[$i]->pro_flag = (!$res_pic==null);

                    $res_data = DB::table('instrument_process_ chrysanthemum_ character')
                        ->where('cultivar_id', '=', $result[$i]->cultivar_id)
                        ->where('plant_id', '=', $result[$i]->plant_id)->where('date', '=', $result[$i]->pic_date)
                        ->where('angle', '=', $result[$i]->angle)
                        ->where('revolution_num', '=', $result[$i]->revolution_num)->get();
                    $result[$i]->data_flag = (!$res_data==null);
                }
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
     * �����û�ѡ���ɸѡ��Ϣ��ȡԭʼͼƬ����Ϣ������
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
                // ����ѯ�����Ϊ�գ������ԭʼͼƬ��Ϣ�жϸ�ͼƬ�Ƿ��ж�Ӧ��LBPͼƬ����ֵ����
                for ($i = 0; $i < count($result); $i++){
                    $res_pic = DB::table('instrument_process_pictures')
                        ->where('cultivar_id', '=', $result[$i]->cultivar_id)
                        ->where('plant_id', '=', $result[$i]->plant_id)->where('pic_date', '=', $result[$i]->pic_date)
                        ->where('angle', '=', $result[$i]->angle)
                        ->where('revolution_num', '=', $result[$i]->revolution_num)->get();
                    $result[$i]->pro_flag = (!$res_pic==null);

                    $res_data = DB::table('instrument_process_ chrysanthemum_ character')
                        ->where('cultivar_id', '=', $result[$i]->cultivar_id)
                        ->where('plant_id', '=', $result[$i]->plant_id)->where('date', '=', $result[$i]->pic_date)
                        ->where('angle', '=', $result[$i]->angle)
                        ->where('revolution_num', '=', $result[$i]->revolution_num)->get();
                    $result[$i]->data_flag = (!$res_data==null);
                }
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

    /**
     * @param Request $request
     * @return array
     * ��ȡ���������ԭʼͼƬ������ͼ
     */
    public function getOriginPic(Request $request){
        $input = $request->all();
        $email = $input['email'];
        $id = $input['id'];
        // �ж��û���ѡ���ͼƬ�Ƿ��Ѿ���������
        $base64_data = array();
        $ids = array();
        foreach ($id as $key => $value){
            $b64 = DB::table('instrument_origin_pictures')->select('base64')
                ->where('id', '=', $value)->get()->toArray();
            // ����ѯ�����Ϊ�գ��򽫽����base64�������飬����Ϊid
            if ($b64[0]->base64) {
                $base64_data[$value] = $b64[0]->base64;
                unset($id[$key]);   // unset��ָ����ֵ������������ɾ��
                $ids = array_values($id);   // ���������½�������
            }
        }
        // �����û�б������ͼƬ�������ݿ���base64Ϊ�գ�������python��ָ��ͼƬ���д���
        if ($ids) {
            // ���췢��python����Ϣ��Ҫ���������
            $post_data = array(
                "email" => $email,
                "id" => $ids
            );
            $emails = explode('@', $email);
            Helper::sendMessage(json_encode($post_data), $this->host . ":4151/pub?topic=instrumentOriginPicProcess");
            $temp = Helper::read_redis('instrument_origin_' . $emails[0], 200, 1);
            $res = json_decode($temp);
            if (isset($res)) {  // ���python����ɹ����򽫴�����ϵ�base64���չ���������鲢����
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