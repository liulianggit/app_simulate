<?php

namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\Users;
use App\model\asset;
use App\model\userinfo;
use App\model\webconfig;
use App\model\lev;
use App\model\margin;
use App\model\systerm;
use App\model\notice;
use App\Http\Controllers\common\CommonController;

class AssetController extends CommonController
{
	/**
     * 获取个人信息（零钱包余额等信息）
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_info(Request $request)
    {
    	$uid = $request->input('uid');
    	$uid = $this->get_input($uid);
    	if(empty($uid)){
    		return $this->jsonOut('2','请传入用户id');
    	}
    	$users = userinfo::where('uid',$uid)->first();
    	if(!empty($users)){
	    	$params['private_key']    = config('app.private_key');
	        $params['partner_key']    = config('app.partner_key');
	        $params['partner_secret'] = config('app.partner_secret');
	        $params['action']         = 'centre_info';
	        $params['user_id']        = $uid;
	        $params['mt4_id'] = $users->mt4_id;
	        $sign = $this->getSign($params['private_key'], $params); #生成签名
	        $params['sign'] = $sign; 
	        unset($params['partner_key']); # 销毁加密key
	        unset($params['partner_secret']); # 销毁加密key
	        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/centre/info',$params);
	        $res = json_decode($res,true);
	        // return $res;
	        if($res['is_succ']){
	        	$asset = asset::where('uid',$uid)->first();
	        	if(!empty($asset)){
	        		$asset->wallet_balance = $res['data']['wallet_balance'];//零钱包余额
	        	}else{
	        		$asset = new asset;
	        		$asset->wallet_balance = $res['data']['wallet_balance'];//零钱包余额
	        		$asset->uid = $uid;//零钱包余额
	        	}
	            $asset->save();
	            $sys_msg=systerm::where("is_view",0)->count();
				$notice=notice::where("is_view",0)->count();
				if($sys_msg || $notice)
				{
					$data['have_news']=1;
				}else{
					$data['have_news']=0;
				}
	            $data['is_check'] = $res['data']['is_check'];//是否认证
	            $data['wallet_balance'] = $res['data']['wallet_balance'];
	            $data['is_true'] = $res['data']['is_true'];//当前使用账号类型
	            return $this->jsonOut('1','返回成功！',$data);
	        }else{
	            return $this->jsonOut('7',$res['message']);
	        }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 获取个人资产信息
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function asset_info(Request $request)
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
		        $params['action']         = 'centre_asset';
		        $params['user_id']        = $uid;
		        $params['mt4_id'] = $info->mt4_id;
		        $params['type'] = 2;
		        $sign = $this->getSign($params['private_key'], $params); #生成签名
		        $params['sign'] = $sign; 
		        unset($params['partner_key']); # 销毁加密key
		        unset($params['partner_secret']); # 销毁加密key
		        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/v2/centre/asset',$params);
		        $res = json_decode($res,true);
		        if($res['is_succ']){
		        	$asset = asset::where('uid',$uid)->first();
		        	if($asset){
		        		$asset->volume = $res['data']['volume'];
		        		$asset->profit_rate = $res['data']['profit_rate'];
		        		$asset->profit = $res['data']['profit'];
		        		$asset->copy_profit = $res['data']['copy_profit'];
		        		$asset->copy_commission = $res['data']['copy_commission'];
		        		$asset->deposit_amount = $res['data']['deposit_amount'];
		        		$asset->withdraw_amount = $res['data']['withdraw_amount'];
		        		$asset->self_trade = $res['data']['self_trade'];
		        		$asset->self_profit_trade = $res['data']['self_profit_trade'];
		        		$asset->margin = $res['data']['margin'];
		        		$asset->balance = $res['data']['balance'];
		        		$asset->credit = $res['data']['credit'];
		        		$asset->wallet_balance = $res['data']['wallet_balance'];//零钱包余额
		        	}else{
		        		$asset = new asset;
		        		$asset->volume = $res['data']['volume'];
		        		$asset->profit_rate = $res['data']['profit_rate'];
		        		$asset->profit = $res['data']['profit'];
		        		$asset->copy_profit = $res['data']['copy_profit'];
		        		$asset->copy_commission = $res['data']['copy_commission'];
		        		$asset->deposit_amount = $res['data']['deposit_amount'];
		        		$asset->withdraw_amount = $res['data']['withdraw_amount'];
		        		$asset->self_trade = $res['data']['self_trade'];
		        		$asset->self_profit_trade = $res['data']['self_profit_trade'];
		        		$asset->margin = $res['data']['margin'];
		        		$asset->balance = $res['data']['balance'];
		        		$asset->credit = $res['data']['credit'];
		        		$asset->wallet_balance = $res['data']['wallet_balance'];//零钱包余额
		        		$asset->uid = $uid;//零钱包余额
		        	}
		            $asset->save();
		            
		            $data['volume'] = $res['data']['volume'];
		            $data['profit_rate'] = $res['data']['profit_rate'];
		            $data['profit'] = $res['data']['profit'];
		            $data['copy_profit'] = $res['data']['copy_profit'];
		            $data['copy_commission'] = $res['data']['copy_commission'];
		            $data['deposit_amount'] = $res['data']['deposit_amount'];
		            $data['withdraw_amount'] = $res['data']['withdraw_amount'];
		            $data['self_trade'] = $res['data']['self_trade'];
		            $data['self_profit_trade'] = $res['data']['self_profit_trade'];
		            $data['margin'] = $res['data']['margin'];
		            $data['balance'] = $res['data']['balance'];
		            $data['credit'] = $res['data']['credit'];
		            $data['wallet_balance'] = $res['data']['wallet_balance'];
		            return $this->jsonOut('1','返回成功！',$data);
		        }else{
		            return $this->jsonOut('7',$res['message']);
		        }
	    	}else{
	    		$data['volume'] = '0.00';
	            $data['profit_rate'] = '0.00';
	            $data['profit'] = '0.00';
	            $data['copy_profit'] = '0.00';
	            $data['copy_commission'] = '0.00';
	            $data['deposit_amount'] = '0.00';
	            $data['withdraw_amount'] = '0.00';
	            $data['self_trade'] = '0';
	            $data['self_profit_trade'] = '0';
	            $data['margin'] = '0.00';
	            $data['balance'] = '0.00';
	            $data['credit'] = '0.00';
	            $data['wallet_balance'] = '0.00';
	            return $this->jsonOut('1','返回成功！',$data);
	    	}
    	}else{
    		return $this->jsonOut('0','没有此用户！');
    	}
    }

    /**
     * 开仓获取可用保证金
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function asset_open(Request $request)
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
		        $params['action']         = 'centre_asset';
		        $params['user_id']        = $uid;
		        $params['mt4_id'] = $info->mt4_id;
		        $params['type'] = 4;
		        $sign = $this->getSign($params['private_key'], $params); #生成签名
		        $params['sign'] = $sign; 
		        unset($params['partner_key']); # 销毁加密key
		        unset($params['partner_secret']); # 销毁加密key
		        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/v2/centre/asset',$params);
		        $res = json_decode($res,true);
		        if($res['is_succ']){
		        	$data['margin_free'] = $res['data']['margin_free'];//可用保证金
		        	$data['margin_level'] = $res['data']['margin_level'];//保证金比例
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
     * 外汇持仓接口(包含持仓和挂单订单)
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function all_trades(Request $request)
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
		        $params['action']         = 'centre_active_trades';
		        $params['user_id']        = $uid;
		        $params['mt4_id'] = $info->mt4_id;
		        $sign = $this->getSign($params['private_key'], $params); #生成签名
		        $params['sign'] = $sign; 
		        unset($params['partner_key']); # 销毁加密key
		        unset($params['partner_secret']); # 销毁加密key
		        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/centre/active/all_trades',$params);
		        $res = json_decode($res,true);
		        if($res['is_succ']){

		        	$data['pending_trades'] = $res['data']['pending_trades'];//挂单列表
		        	if(!empty($data['pending_trades'])){
		        		foreach ($data['pending_trades'] as $k => $v) {
		        			$data['pending_trades'][$k]['status'] = 0;
							$data['pending_trades'][$k]['open_time']=date("Y-m-d H:i:s",$v['open_time']);
		        			$data['pending_trades'][$k]['contract_size'] = lev::where('symbol','like','%'.substr($v['symbol'], 0,6).'%')->first()['contract_size'];
		        			$tmp = margin::where('symbol',$v['symbol'])->first();
		        			$data['pending_trades'][$k]['margin_initial'] = $tmp['margin_initial'];
		        			$data['pending_trades'][$k]['margin_divider'] = $tmp['margin_divider'];

		        		}
		        	}

		        	$data['open_trades'] = $res['data']['open_trades'];//持仓列表
		        	if(!empty($data['open_trades'])){
		        		foreach ($data['open_trades'] as $k => $v) {
		        			$data['open_trades'][$k]['status'] = 1;
							$data['open_trades'][$k]['open_time']=date("Y-m-d H:i:s",$v['open_time']);
		        			$data['open_trades'][$k]['contract_size'] = lev::where('symbol','like','%'.substr($v['symbol'], 0,6).'%')->first()['contract_size'];
		        			$tmp = margin::where('symbol',$v['symbol'])->first();
		        			$data['open_trades'][$k]['margin_initial'] = $tmp['margin_initial'];
		        			$data['open_trades'][$k]['margin_divider'] = $tmp['margin_divider'];
		        		}
		        	}
		        	$data['all_trades'] = array_merge($data['pending_trades'],$data['open_trades']);
		        	$data['secured_deposit'] = 0;
		        	if(count($data['all_trades'])){
		        		foreach($data['all_trades'] as $key=>$val){  
					    	$dos[$key] = $val['open_time'];  
					    	if(isset($val['margin'])){
					    		$data['secured_deposit'] += $val['margin'];
					    	}
						}  
						array_multisort($dos,SORT_DESC,$data['all_trades']); 
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
     * 获取持仓、平仓订单详情
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function trade_info(Request $request)
    {
    	$uid = $request->input('uid');
    	$uid = $this->get_input($uid);
    	$ticket = $request->input('ticket');
    	$ticket = $this->get_input($ticket);
    	if(empty($uid)){
    		return $this->jsonOut('2','请传入用户id');
    	}else if(empty($ticket)){
    		return $this->jsonOut('4','请传入订单号');
    	}

    	$rs = Users::find($uid);
    	if($rs){
    		$info = userinfo::where('uid',$uid)->first();
    		if(!empty($info->mt4_id)){
    			$params['private_key']    = config('app.private_key');
		        $params['partner_key']    = config('app.partner_key');
		        $params['partner_secret'] = config('app.partner_secret');
		        $params['action']         = 'centre_active_trades';
		        $params['user_id']        = $uid;
		        $params['mt4_id'] = $info->mt4_id;
		        $params['ticket'] = $ticket;
		        $sign = $this->getSign($params['private_key'], $params); #生成签名
		        $params['sign'] = $sign; 
		        unset($params['partner_key']); # 销毁加密key
		        unset($params['partner_secret']); # 销毁加密key
		        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/centre/trade/info',$params);
		        $res = json_decode($res,true);
		        if($res['is_succ']){
		        	$data = $res['data'];
					$data['open_time'] = date('Y-m-d H:i:s',$data['open_time']);
					if($data['close_time']){
						$data['close_time'] = date('Y-m-d H:i:s',$data['close_time']);
					}else{
						$data['close_time'] = '持仓中';
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
     * 历史交易记录
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function history_trade(Request $request)
    {
    	$uid = $request->input('uid');
    	$uid = $this->get_input($uid);
    	$limit = $request->input('limit')?$request->input('limit'):10;
    	$limit = $this->get_input($limit);
    	$offset = $request->input('offset');
    	$offset = $this->get_input($offset);
    	if(empty($uid)){
    		return $this->jsonOut('2','请传入用户id');
    	}else if(empty($limit)){
    		return $this->jsonOut('4','请传入查询条数');
    	}else if($offset==''){
    		return $this->jsonOut('5','请传入偏移量');
    	}

    	$rs = Users::find($uid);
    	if($rs){
    		$info = userinfo::where('uid',$uid)->first();
    		if(!empty($info->mt4_id)){
    			$params['private_key']    = config('app.private_key');
		        $params['partner_key']    = config('app.partner_key');
		        $params['partner_secret'] = config('app.partner_secret');
		        $params['action']         = 'centre_past_trades';
		        $params['user_id']        = $uid;
		        $params['mt4_id'] = $info->mt4_id;
		        $params['limit'] = $limit;
		        $params['offset'] = $offset;
		        $sign = $this->getSign($params['private_key'], $params); #生成签名
		        $params['sign'] = $sign; 
		        unset($params['partner_key']); # 销毁加密key
		        unset($params['partner_secret']); # 销毁加密key
		        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/centre/past/trades',$params);
		        $res = json_decode($res,true);
		        if($res['is_succ']){
		        	$data = $res['data'];
		        	foreach ($data['records'] as $k=>$v) {
                        $data['records'][$k]['open_time'] = date('Y-m-d H:i:s',$v['open_time']);
                        $data['records'][$k]['close_time'] = date('Y-m-d H:i:s',$v['close_time']);
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
}
