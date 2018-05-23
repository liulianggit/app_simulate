<?php
namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\Users;
use App\model\asset;
use App\model\userinfo;
use App\model\webconfig;
use App\model\order;
use App\Http\Controllers\common\CommonController;

class WalletController extends CommonController
{

    /**
     * 交易明细
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function wallet_histories(Request $request)
    {
    	$uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $offset = $request->input('offset');
        $offset = $this->get_input($offset);
        $limit = $request->input('limit');
    	$limit = $this->get_input($limit);
    	if(empty($uid)){
    		return $this->jsonOut('2','请传入用户id');
    	}
        if($request->has('offset') && !is_null($request->input('offset'))){
            $offset = $request->input('offset');
        }else{
            $offset = 0;
        }
        if($request->has('limit') && !is_null($request->input('limit'))){
            $limit = $request->input('limit');
        }else{
            $limit = 10;
        }

    	$rs = Users::find($uid);
    	if($rs){
    		$info = userinfo::where('uid',$uid)->first();
    		if(!empty($info->mt4_id)){
    			$params['private_key']    = config('app.private_key');
		        $params['partner_key']    = config('app.partner_key');
		        $params['partner_secret'] = config('app.partner_secret');
		        $params['action']         = 'wallet_histories';
		        $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['offset'] = $offset;
		        $params['limit'] = $limit;
		        $sign = $this->getSign($params['private_key'], $params); #生成签名
		        $params['sign'] = $sign; 
		        unset($params['partner_key']); # 销毁加密key
		        unset($params['partner_secret']); # 销毁加密key
		        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/wallet/histories',$params);
		        $res = json_decode($res,true);
		        if($res['is_succ']){
		        	$data = $res['data'];
                    foreach ($data['records'] as $k => $v) {
                        $data['records'][$k]['created_at'] = date('Y-m-d H:i:s',$v['created_at']);
                    }
		        	return $this->jsonOut('1','返回成功！',$data);
		        }else{
		        	return $this->jsonOut('7',$res['message']);
		        }
	    	}else{
	    		return $this->jsonOut('3','请先开户！');
	    	}
    	}else{
    		return $this->jsonOut('0','没有此用户！');
    	}
    }

    /**
     * 可出金金额
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function valid_balance(Request $request)
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
                $params['action']         = 'wallet_valid_balance';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/wallet/valid_balance',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    return $this->jsonOut('1','返回成功！',$data);
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 提现
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function wallet_withdraw(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $amount = $request->input('amount');
        $amount = $this->get_input($amount);
        $order_id = $request->input('order_id');
        $order_id = $this->get_input($order_id);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id');
        }else if(empty($amount)){
            return $this->jsonOut('4','请输入提现金额');
        }else if(empty($order_id)){
            return $this->jsonOut('5','请输入订单号');
        }else if($amount<20){
            return $this->jsonOut('6','提现金额不能低于20美金');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'wallet_withdraw';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['amount'] = $amount;
                $params['order_id'] = $order_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/wallet/withdraw',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    return $this->jsonOut('1','返回成功！',$data);
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 划入交易账户
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function wallet_deposit(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $amount = $request->input('amount');
        $amount = $this->get_input($amount);
        $order_id = $request->input('order_id');
        $order_id = $this->get_input($order_id);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id');
        }else if(empty($amount)){
            return $this->jsonOut('4','请输入转入金额');
        }else if(empty($order_id)){
            return $this->jsonOut('5','请输入订单号');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'wallet_deposit';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['amount'] = $amount;
                $params['order_id'] = $order_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/wallet/deposit',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    $order = new order;
                    $order->uid = $uid;
                    $order->amount = $amount;
                    $order->order_id = $order_id;
                    $order->o_type = 1;
                    $order->add_time = time();
                    $order->status = 0;
                    $order->save();
                    return $this->jsonOut('1','充值已提交，审核通过后零钱将划入交易账户',$data);
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
            }else{
                return $this->jsonOut('3','请先开户！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    
}