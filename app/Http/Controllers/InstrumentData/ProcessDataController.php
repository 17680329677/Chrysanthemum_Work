<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/18/018
 * Time: 13:08
 */
namespace App\Http\Controllers\InstrumentData;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessDataController extends Controller{

    /**
     * @param Request $request
     * @return array
     * 获取仪器生成的数据信息
     */
    public function getProcessData(Request $request){
        // 获取用户请求的数据
        $email = $request->get('email');
        $cultivar_id = $request->get('cultivar_id');
        $plant_id = $request->get('plant_id');
        $pic_date = $request->get('pic_date');
        $angle = $request->get('angle');
        $revolution_num = $request->get('revolution_num');
        try{
            $result = DB::table('instrument_process_ chrysanthemum_ character')
                ->where('cultivar_id', '=', $cultivar_id)
                ->where('plant_id', '=', $plant_id)->where('date', '=', $pic_date)
                ->where('angle', '=', $angle)
                ->where('revolution_num', '=', $revolution_num)
                ->get()->toArray();
            // 通过id和name的联合表查询品种名称
            $cultivar_name = DB::table('id_name')
                ->select('cultivar_name')
                ->where('cultivar_id', '=', $cultivar_id)
                ->get()->toArray();
            $result[0]->cultivar_name = $cultivar_name[0]->cultivar_name;
            return $data = [
                'status' => 'success',
                'data' => $result[0]
            ];
        }catch (Exception $exception){
            return $data = [
                'status' => 'failed',
                'reason' => $exception->getMessage()
            ];
        }

    }
}