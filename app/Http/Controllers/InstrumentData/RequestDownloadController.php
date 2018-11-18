<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/18/018
 * Time: 13:42
 */
namespace App\Http\Controllers\InstrumentData;

use App\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RequestDownloadController extends Controller{
    protected $host = '127.0.0.1';

    public function packAndDownLoad(Request $request){
        $email = $request->get('email');
        $ids = $request->get('ids');

        $post_data = array(
            'email' => $email,
            'ids' => $ids
        );
        Helper::sendMessage(json_encode($post_data), $this->host . ":4151/pub?topic=instrumentPack");
    }
}