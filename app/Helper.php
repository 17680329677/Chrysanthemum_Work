<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/17/017
 * Time: 14:20
 * Function: 部分公共方法的抽取
 */
namespace App;

use Illuminate\Support\Facades\Redis;

class Helper {
    /**
     * @param $post_data
     * @param $url
     * 用于和python部分的nsq通信
     */
    static function sendMessage($post_data, $url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($curl);
        curl_close($curl);
    }

    static function read_redis($key, $flag_time=50, $sleep_time=1){
        $flag = 0;
        $data = null;
        sleep($sleep_time);
        while ($flag < $flag_time){
            if (!isset($data)) {
                $data = Redis::get($key);
                sleep($sleep_time);
                $flag++;
            }else {
                Redis::del($key);
                return $data;
            }
        }
        return null;
    }

    // 将图片转换为base64编码
    static function base64EncodeImage($image_file){
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }

}
