<?php

namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\common\CommonController;

class BreedController extends CommonController
{
	/**
     * 生成融云签名
     * @param  string $appSecret [description]
     * @return [type]            [description]
     */
    public function index(Request $request)
    {
    	$userId = $request->input('userId');
    	$name = $request->input('name');
    	$portraitUri = $request->input('portraitUri');
    	$params = ['userId'=>$userId,'name'=>$name,'portraitUri'=>$portraitUri];
    	$data = $this->send_curl('/user/getToken.json',$params,'urlencoded','im','POST');

    	return $data;
    }
}
