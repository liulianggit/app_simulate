<?php

namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\common\CommonController;
use App\model\bank;
use Log;


class CityController extends CommonController
{

	public function get_bank()
	{
		$data = bank::select('title')->where('bank_img','<>','')->get();
		return $this->jsonOut('1','返回成功',$data);
	}
}