<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/11/011
 * Time: 14:46
 */
namespace App\Http\Controllers\User;


use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolesController extends Controller{
    // 角色信息控制器

    public function createRole(Request $request){
        $this->validate($request, [
//            'name' => 'required|unique:roles,name',
            'display_name' => 'required',
            'description' => 'required',
            'permission' => 'required',
        ]);

        $role = new Role();
        $role->name = $request->input('name');
        $role->display_name = $request->input('display_name');
        $role->description = $request->input('description');
        $role->save();
//        Log::info('error出错了');
        foreach ($request->input('permission') as $key => $value) {
//            DB::table('Permissions')->get()
            $role->attachPermission($value);
        }


        return $data=[
            'status'=>"success",
            'data'=>[],
            'reason'=>""
        ];

    }
}