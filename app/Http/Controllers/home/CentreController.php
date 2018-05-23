<?php
namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\Users;
use App\model\asset;
use App\model\userinfo;
use App\model\webconfig;
use App\Http\Controllers\common\CommonController;

class CentreController extends CommonController
{

    /**
     * 用户交易概况
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function trading_profile(Request $request)
    {
    	$uid = $request->input('uid');
    	$uid = $this->get_input($uid);
    	if(empty($uid)){
    		return $this->jsonOut('2','请传入用户id');
    	}

    	$rs = Users::find($uid);
    	if($rs){
    		$info = userinfo::where('uid',$uid)->first();
    		if(!empty($info->mt4_id)){
    			$params['private_key']    = config('app.private_key');
		        $params['partner_key']    = config('app.partner_key');
		        $params['partner_secret'] = config('app.partner_secret');
		        $params['action']         = 'centre_trading_profile';
		        $params['user_id']        = $uid;
		        $params['mt4_id'] = $info->mt4_id;
		        $sign = $this->getSign($params['private_key'], $params); #生成签名
		        $params['sign'] = $sign; 
		        unset($params['partner_key']); # 销毁加密key
		        unset($params['partner_secret']); # 销毁加密key
		        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/centre/trading_profile',$params);
		        $res = json_decode($res,true);
		        if($res['is_succ']){
		        	$data = $res['data'];
		        	return $this->jsonOut('1','返回成功！',$data);
		        }else{
		        	return $this->jsonOut('0',$res['message']);
		        }
	    	}else{
	    		return $this->jsonOut('3','请先开户！');
	    	}
    	}else{
    		return $this->jsonOut('0','没有此用户！');
    	}
    }

    /**
     * 用户交易走势图
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function trading_trend(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'centre_trading_trend';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/centre/trading_trend',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    return $this->jsonOut('1','返回成功！',$data);
                }else{
                    return $this->jsonOut('0',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 用户主要交易品种
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function trading_symbols(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'centre_trading_symbols';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/centre/trading_symbols',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    return $this->jsonOut('1','返回成功！',$data);
                }else{
                    return $this->jsonOut('0',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }
}