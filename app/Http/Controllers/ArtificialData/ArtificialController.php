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

class ArtificialController extends Controller {
    /**
     * 人工拍摄数据性状控制器
     */

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
            return $data=[
                'status'=>'success',
                'data'=>$info,
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

        // 需要检索数值范围的指标
        $plant_height = $input['plant_height'];     // 植株高度
        $flower_diameter = $input['flower_diameter'];       // 花径
        $disc_florets_diameter = $input['disc_florets_diameter'];       // 筒状花部直径
        $petal_length = $input['petal_length'];     // 花瓣长度
        $petal_width = $input['petal_width'];       // 花瓣宽度
        $leaf_length = $input['leaf_length'];       // 叶片长
        $leaf_width = $input['leaf_width'];         // 叶片宽

        $results = DB::table('artificial_character')
            ->whereIn('ray_florets_flaps', $input['ray_florets_flaps'])
            ->whereIn('flower_type', $input['flower_type'])
            ->whereIn('classification_of_cultivar', $input['classification_of_cultivar'])
            ->whereIn('color_system', $input['color_system'])
            ->whereIn('age_of_cultivar', $input['age_of_cultivar'])
            ->get()->toArray();

        if ($results){
            return $data=[
                'status'=>'success',
                'data'=>$results,
                'reason'=>"t"
            ];
        }else {
            return $data=[
                'status'=>'failed',
                'data'=>'',
                'reason'=>"not found!"
            ];
        }
    }
}