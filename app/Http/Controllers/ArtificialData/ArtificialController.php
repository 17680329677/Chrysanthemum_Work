<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15/015
 * Time: 10:56
 */
namespace App\Http\Controllers\ArtificialData;

use App\Http\Controllers\Controller;
use App\Models\Artificial_Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helper;

class ArtificialController extends Controller {
    /**
     * 人工拍摄数据性状控制器
     */
    protected $host = '127.0.0.1';

    /**
     * @return array
     * 返回数据库中所有人工数据
     */
    public function getAll() {
        $characterList = DB::table('artificial_character')->get()->toArray();
        return $data=[
            'status'=>'success',
            'data'=>$characterList,
            'reason'=>""
        ];
    }

    /**
     * @param $postdata
     * @param $url
     * @param $redis_key
     * @return array
     * 和python部分处理人工图片的方法传输消息
     */
    static function artificial_python($postdata, $url, $redis_key) {
        // 给python部分发送图片检索和处理的消息
        Helper::sendMessage(json_encode($postdata), $url);
        $temp = Helper::read_redis($redis_key, 200, 1);
        $res = json_decode($temp);
        if (isset($res) and $res->status == 'success'){
            return $process_res = [
                'status' => 'success'
            ];
        }else{
            return $process_res = [
                'status' => 'failed',
                'reason' => $res->reason
            ];
        }
    }


    /**
     * @param Request $request
     * @return array
     * 根据用户输入的品种名进行模糊检索
     */
    public function getCharacterByName(Request $request) {
        // 获取请求发送的数据
        $input = $request->all();

        $info = DB::table('artificial_character')
            ->where('cultivar_name', 'like', '%'.$input['cultivar_name'].'%')
            ->get()->toArray();
        if ($info) {
            $cultivar_id = [];
            for ($i = 0; $i < count($info); $i++){
                $id = $info[$i]->id;
                array_push($cultivar_id, $id);
            }

            return $data=[
                'status'=>'success',
                'data'=>$info,
                'cultivar_id'=>$cultivar_id,
                'reason'=>""
            ];
        }else {
            return $data=[
                'status'=>'failed',
                'data'=>'',
                'reason'=>"this name is not found!"
            ];
        }
    }

    public function getCharacterByIndex(Request $request){
        // 获取请求端发送的所有数据
        $input = $request->all();
        // 文字性指标
        $indexList = array();
        $indexList['ray_florets_flaps'] = $input['ray_florets_flaps'];
        $indexList['flower_type'] = $input['flower_type'];
        $indexList['classification_of_cultivar'] = $input['classification_of_cultivar'];
        $indexList['color_system'] = $input['color_system'];
        $indexList['age_of_cultivar'] = $input['age_of_cultivar'];


        // 需要检索数值范围的指标
        $numList = array();
        $numList['plant_height'] = $input['plant_height'];
        $numList['flower_diameter'] = $input['flower_diameter'];
        $numList['disc_florets_diameter'] = $input['disc_florets_diameter'];
        $numList['petal_length'] = $input['petal_length'];
        $numList['petal_width'] = $input['petal_width'];
        $numList['leaf_length'] = $input['leaf_length'];
        $numList['leaf_width'] = $input['leaf_width'];


        $results = DB::table('artificial_character')
            // 使用匿名函数动态添加指标，空指标不做检索
            ->where(
                function ($query) use ($indexList){
                    foreach ($indexList as $key => $value){
                        if ($indexList[$key] != null){
                            $query->whereIn($key, $value);
                        }
                    }
                }
            )
            ->where(
                function ($query) use ($numList){
                    foreach ($numList as $key => $value){
                        if ($numList[$key] != null){
                            $query->whereBetween($key, $value);
                        }
                    }
                }
            )
            ->get()->toArray();

        // 获取检索结果的id
        $cultivar_id = [];
        for ($i = 0; $i < count($results); $i++){
            $id = $results[$i]->id;
            array_push($cultivar_id, $id);
        }

        if ($results){
            return $data=[
                'status'=>'success',
                'data'=>$results,
                'cultivar_id' => $cultivar_id,
                'reason'=>""
            ];
        }else {
            return $data=[
                'status'=>'failed',
                'data'=>'',
                'reason'=>"not found!"
            ];
        }
    }

    public function picProcess(Request $request){
        // 获取用户请求的数据
        $input = $request->all();
        $cultivar_id = $input['cultivar_id'];
        $email = $input['email'];
        // 判断用户所选的品种是否已经经过处理
        $flag = false;
        $base64_data = array();
        $ids = array();
        // 将未经过python处理的品种的id保存起来，以便向python发送消息
        foreach ($cultivar_id as $key => $value){
            $result = DB::table('artificial_shot_pictures')->select('base64')
                ->where('cultivar_id', '=', $value)->get()->toArray();
            for ($i = 0; $i < count($result); $i++){
                if ($result[$i]->base64 == null){
                    array_push($ids, $value);
                    break;
                }
            }
        }

        // 如果未经处理的id集不为空，则向python发送处理图片的请求
        if ($ids) {
            $post_data = array(
                "email" => $email,
                "id" => $ids
            );
            $process_res = self::artificial_python($post_data, $this->host . ":4151/pub?topic=artificialPicProcess", $email);
            if ($process_res['status'] == 'success'){
                $flag = true;   // 若python处理成功，则将标志flag设为真值
            }else {
                return $data = [
                    'status' => 'failed',
                    'reason' => $process_res['reason']
                ];
            }
        }else {     // 若图片都已经经过处理，也将flag设为真值
            $flag = true;
        }

        // 若flag为true（python处理成功或不需要python处理） 且用户请求的品种不为空
        if ($flag and $cultivar_id){
            foreach ($cultivar_id as $key => $value){
                $res = DB::table('artificial_shot_pictures')->select('base64')
                    ->where('cultivar_id', '=', $value)->get()->toArray();
                $base64_data[$value] = $res;
            }
            return $data = [
                'status' => 'success',
                'pic' => $base64_data
            ];
        }else {
            return $data = [
                'status' => 'failed',
                'reason' => '获取图片失败'
            ];
        }

    }

}