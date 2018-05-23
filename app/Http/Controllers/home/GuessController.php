<?php

namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\common\CommonController;
use App\model\guess;
use App\model\gutime;

class GuessController extends CommonController
{
	/**
	 * 轮播加载最新的10条竞猜数据
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
    public function index()
    {
    	$guess = guess::select('phone','guess_no','add_time')->orderBy('add_time','desc')->limit(10)->get();
    	if(count($guess)>0){
    		foreach ($guess as $k => $v) {
    			$guess[$k]['add_time'] = date('Y-m-d H:i:s',$v['add_time']);
    		}
    	}
    	return $this->jsonOut('1','获取成功',$guess);
    	
    }

    /**
     * 竞猜
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function play_guess(Request $request)
    {
    	$phone = $request->input('phone','');
    	$guess_no = $request->input('guess_no','');
    	if(empty($phone) || empty($guess_no)){
    		return $this->jsonOut('2','请填写手机号或竞猜号码!');
    	}
    	if(!preg_match("/^1[34578]\d{9}$/",$phone))
        {
           return $this->jsonOut('2','手机号格式不正确');
        }
    	//获取竞猜时间
    	$gutime = gutime::orderBy('id','desc')->first();
    	if(time() < $gutime['start_time'] || time() > $gutime['end_time']){
    		return $this->jsonOut('2','不在竞猜时间内！',$gutime);
    	}

    	$res = guess::where('phone',$phone)->first();
    	if($res){
    		if($res->guess_no2){
    			return $this->jsonOut('2','您的竞猜此数不足！');
	    	}
	    	if($res->share_stu != 1){
	    		return $this->jsonOut('2','分享后可获得一次竞猜机会哦！');
	    	}else{
	    		$res->guess_no2 = $guess_no;
	    		$res->add_time2 = time();
	    		$res->save();
	    		if($res->save()){
		    		return $this->jsonOut('1','竞猜成功！');
		    	}else{
		    		return $this->jsonOut('2','竞猜失败！');
		    	}
	    	}
    	}else{
    		$guess = new guess;
    		$guess->phone = $phone;
    		$guess->guess_no = $guess_no;
    		$guess->add_time = time();
    		if($guess->save()){
	    		return $this->jsonOut('1','竞猜成功！');
	    	}else{
	    		return $this->jsonOut('2','竞猜失败！');
	    	}
    	}
    	
    }

    /**
     * 修改分享状态
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function change_stu(Request $request)
    {
    	$phone = $request->input('phone','');
    	if(empty($phone)){
    		return $this->jsonOut('2','缺少参数');
    	}
    	if(!preg_match("/^1[34578]\d{9}$/",$phone))
        {
           return $this->jsonOut('2','手机号格式不正确');
        }
    	//获取竞猜时间
    	$gutime = gutime::orderBy('id','desc')->first();
    	$guess = guess::where('phone',$phone)->whereBetween('add_time',[$gutime->start_time,$gutime->end_time])->first();
    	if($guess){
    		if($guess->share_stu){
    			return $this->jsonOut('1','分享成功！');
    		}else{
    			$guess->share_stu = 1;
    			if($guess->save()){
    				return $this->jsonOut('1','分享成功！');
    			}else{
    				return $this->jsonOut('2','分享失败！');
    			}
    		}
    	}else{
    		return $this->jsonOut('1','分享成功！');
    	}
    	
    }
}