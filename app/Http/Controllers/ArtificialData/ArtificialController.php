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

        $path = "";
        $cultivar_id = '';
        $pic = [];
        if (isset($res)){
            if ($res->status == 'success'){
                $path = $res->path;
                $cultivar_id = $res->id;
                $email = $res->email;
                for($i = 0; $i < count($cultivar_id); $i++) {
                    $pic['cultivar' . $cultivar_id[$i]] = [];
                    $base64_pic = DB::table('pic_' . $email . '_artificial')
                        ->select('base64')->where('cultivar_id', '=', $cultivar_id[$i])
                        ->get()->toArray();
                    array_push($pic['cultivar' . $cultivar_id[$i]], $base64_pic);
                }
            }
        }

//        for($i = 0; $i < count($cultivar_id); $i++){
//            $files = scandir($path . '\\' . $cultivar_id[$i]);
//            $pic['cultivar' . $cultivar_id[$i]] = [];
//            foreach ($files as $file){
//                if ($file != '.' && $file != '..'){
//                    // $file = iconv("GBK", "UTF-8//IGNORE", $file);
//                    array_push($pic['cultivar' . $cultivar_id[$i]], $file);
//                }
//            }
//        }


        $process_res = [
            'path' => $path,
            'pic' => $pic
        ];

        return $process_res;
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
//            ->whereIn('ray_florets_flaps', $input['ray_florets_flaps'])
//            ->whereIn('flower_type', $input['flower_type'])
//            ->whereIn('classification_of_cultivar', $input['classification_of_cultivar'])
//            ->whereIn('color_system', $input['color_system'])
//            ->whereIn('age_of_cultivar', $input['age_of_cultivar'])
            ->where(
                function ($query) use ($indexList){
                    foreach ($indexList as $key => $value){
                        if ($indexList[$key] != null){
                            $query->whereIn($key, $value);
                        }
                    }
                }
            )
            // 使用匿名函数动态添加指标，空指标不做检索
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

        $post_data = array(
            "email" => $email,
            "id" => $cultivar_id
        );
        $process_res = self::artificial_python($post_data, $this->host . ":4151/pub?topic=artificialPicProcess", $email);
        $path = $process_res['path'];
        $pic = $process_res['pic'];
//        $base64 = $process_res['base64'];


        return $data = [
            'status'=>'success',
            'cultivar_id'=>$cultivar_id,
            'path'=>$path,
            'pic'=>$pic
        ];
    }
}