<?php
namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\Users;
use App\model\asset;
use App\model\order;
use App\model\userinfo;
use App\model\webconfig;
use App\model\bank;
use App\model\notice;
use App\Http\Controllers\common\CommonController;

class PaymentController extends CommonController
{
	/**
     * 充值/提现记录
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function payment_histories(Request $request)
    {
    	$uid = $request->input('uid');
    	$uid = $this->get_input($uid);
    	$direction = $request->input('direction');
    	$direction = $this->get_input($direction);
    	$limit = $request->input('limit');
    	$limit = $this->get_input($limit);
    	$offset = $request->input('offset');
    	$offset = $this->get_input($offset);
    	$status = $request->input('status','');
    	$status = $this->get_input($status);
    	if(empty($uid)){
    		return $this->jsonOut('2','请传入用户id');
    	}else if($direction==""){
    		return $this->jsonOut('4','请传入类型');
    	}else if(empty($limit)){
    		return $this->jsonOut('5','请传入要查询的条数');
    	}else if($offset==''){
    		return $this->jsonOut('6','请传入偏移量');
    	}

    	$rs = Users::find($uid);
    	if($rs){
    		$info = userinfo::where('uid',$uid)->first();
    		if(!empty($info->mt4_id)){
    			$params['private_key']    = config('app.private_key');
		        $params['partner_key']    = config('app.partner_key');
		        $params['partner_secret'] = config('app.partner_secret');
		        $params['action']         = 'payment_histories';
		        $params['user_id']        = $uid;
		        $params['mt4_id'] = $info->mt4_id;
		        $params['direction'] = $direction;
		        $params['limit'] = $limit;
		        $params['offset'] = $offset;
		        $params['status'] = $status;
		        $sign = $this->getSign($params['private_key'], $params); #生成签名
		        $params['sign'] = $sign; 
		        unset($params['partner_key']); # 销毁加密key
		        unset($params['partner_secret']); # 销毁加密key
		        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/payment/histories',$params);
		        $res = json_decode($res,true);
		        if($res['is_succ']){
		        	$data = $res['data'];
		        	return $this->jsonOut('1','返回成功！',$data);
		        }else{
		        	return $this->jsonOut('4',$res['message']);
		        }
	    	}else{
	    		return $this->jsonOut('3','请先开户！');
	    	}
    	}else{
    		return $this->jsonOut('0','没有此用户！');
    	}
    }

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
		        	return $this->jsonOut('4',$res['message']);
		        }
	    	}else{
	    		return $this->jsonOut('3','请先开户！');
	    	}
    	}else{
    		return $this->jsonOut('0','没有此用户！');
    	}
    }

    /**
     * 入金条件限制
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function deposit_limits(Request $request)
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
		        $params['action']         = 'payment_deposit_limits';
		        $params['user_id']        = $uid;
		        $params['mt4_id'] = $info->mt4_id;
		        $sign = $this->getSign($params['private_key'], $params); #生成签名
		        $params['sign'] = $sign; 
		        unset($params['partner_key']); # 销毁加密key
		        unset($params['partner_secret']); # 销毁加密key
		        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/payment/deposit/limits',$params);
		        $res = json_decode($res,true);
		        if($res['is_succ']){
		        	$data = $res['data'];
		        	return $this->jsonOut('1','返回成功！',$data);
		        }else{
		        	return $this->jsonOut('4',$res['message']);
		        }
	    	}else{
	    		return $this->jsonOut('3','请先开户！');
	    	}
    	}else{
    		return $this->jsonOut('0','没有此用户！');
    	}
    }

    /**
     * 上传大额入金凭证
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function payment_evidence(Request $request)
    {
    	//获取文件对象
		$file = preg_replace("/\s/",'+',$request->input('up_file'));
		//获取uid
		$uid = $request->input('uid');
		$uid = $this->get_input($uid);
		if(empty($file)){
			return $this->jsonOut('2','请选择上传图片');
		}else if(empty($uid)){
			return $this->jsonOut('3','请传入用户id！');
		}
		$rs = Users::find($uid);
    	if($rs){
    		$info = userinfo::where('uid',$uid)->first();
    		if(!empty($info->mt4_id)){
    			$params['private_key']    = config('app.private_key');
		        $params['partner_key']    = config('app.partner_key');
		        $params['partner_secret'] = config('app.partner_secret');
		        $params['action']         = 'payment_evidence';
		        $params['user_id']        = $uid;
		        $params['mt4_id'] = $info->mt4_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['file'] = $file;
		        $params['sign'] = $sign; 
		        unset($params['partner_key']); # 销毁加密key
		        unset($params['partner_secret']); # 销毁加密key
                $re_h5= $this->curl_request('get',"https://h5dev.open.tigerwit.com/third_napi");
                $re_h5=json_decode($re_h5,true);
                if($re_h5['is_succ']=='true')
                {
                    $h5_path=$re_h5['data']['evidence'];
                }else{
                    return   $this->jsonOut('3','h5地址获取错误');
                }
		        $res = $this->curl_request('post',$h5_path,$params);
		        $res = json_decode($res,true);
		        if($res['is_succ']){
		        	$data = $res['data'];
		        	return $this->jsonOut('1','返回成功！',$data);
		        }else{
		        	return $this->jsonOut('4',$res['message']);
		        }
	    	}else{
	    		return $this->jsonOut('3','请先开户！');
	    	}
    	}else{
    		return $this->jsonOut('0','没有此用户！');
    	}
    }

    /**
     * 申请入金【手机客户端入金】
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function payment_deposit_app(Request $request)
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
            return $this->jsonOut('4','请输入入金金额');
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
                $params['action']         = 'payment_deposit_app';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['amount'] = $amount;
                $params['order_id'] = $order_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); #销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/payment/deposit/app',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    //写入order表
                    $order=new order();
                    $order->uid=$uid;
                    $order->amount=$amount;
                    $order->order_id=$order_id;
                    $order->o_type=1;
                    $order->add_time=time();
                    $order->status=0;
                    if($order->save()) {
                        return $this->jsonOut('1', '返回成功！', $data);
                    }else{
                        return $this->jsonOut('6', '订单写入失败！');
                    }
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
     * 申请入金【web端入金】
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function payment_deposit_web(Request $request)
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
            return $this->jsonOut('4','请输入入金金额');
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
                $params['action']         = 'payment_deposit';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['amount'] = $amount;
                $params['order_id'] = $order_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/payment/deposit',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    //写入order表
                    $order=new order();
                    $order->uid=$uid;
                    $order->amount=$amount;
                    $order->order_id=$order_id;
                    $order->o_type=1;
                    $order->add_time=time();
                    $order->status=0;
                    if($order->save()) {
                        return $this->jsonOut('1', '返回成功！', $data);
                    }else{
                        return $this->jsonOut('6', '订单写入失败！');
                    }
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
     * 申请入金【电汇入金】
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function payment_deposit_transfer(Request $request)
    {
    	$uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $amount = $request->input('amount');
        $amount = $this->get_input($amount);
        $order_id = $request->input('order_id');
        $order_id = $this->get_input($order_id);
        $file = preg_replace("/\s/",'+',$request->input('file'));
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id');
        }else if(empty($amount)){
            return $this->jsonOut('4','请输入入金金额');
        }else if(empty($order_id)){
            return $this->jsonOut('5','请输入订单号');
        }else if(empty($file)){
        	return $this->jsonOut('6','请选择上传图片');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'payment_deposit_transfer';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['amount'] = $amount;
                $params['order_id'] = $order_id;
                $params['file'] = $file;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/payment/deposit/transfer',$params);
                $res = json_decode($res,true);
                // return $this->jsonOut('1', '返回成功！', $res);
                if($res['is_succ']){
                    //写入order表
                    $order=new order();
                    $order->uid=$uid;
                    $order->amount=$amount;
                    $order->order_id=$order_id;
                    $order->o_type=1;
                    $order->add_time=time();
                    $order->status=0;
                    if($order->save()) {
                        return $this->jsonOut('1', '返回成功！');
                    }else{
                        return $this->jsonOut('6', '订单写入失败！');
                    }
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
     * 获取支持出金银行列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_bank_names(Request $request)
    {
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'bank_names';
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/bank_names',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('7',$res['message']);
        }
            
    }

    /**
     * 申请出金
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function payment_payout(Request $request)
    {
    	$uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $amount = $request->input('amount');
        $amount = $this->get_input($amount);
        $bank_name = $request->input('bank_name');
        $bank_name = $this->get_input($bank_name);
        $bank_addr = $request->input('bank_addr');
        $bank_addr = $this->get_input($bank_addr);
        $card = $request->input('card');
        $card = $this->get_input($card);
        $province = $request->input('province');
        $province = $this->get_input($province);
        $city = $request->input('city');
        $city = $this->get_input($city);
        $order_id = $request->input('order_id');
        $order_id = $this->get_input($order_id);
        if(empty($uid) || empty($bank_name) || empty($bank_addr) || empty($card) || empty($province) || empty($city) || empty($order_id)){
            return $this->jsonOut('2','缺少参数');
        }else if(empty($amount)){
            return $this->jsonOut('4','请输入入金金额');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'payment_payout';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['amount'] = $amount;
                $params['bank_name'] = $bank_name;
                $params['bank_addr'] = $bank_addr;
                $params['card'] = $card;
                $params['province'] = $province;
                $params['city'] = $city;
                $params['order_id'] = $order_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/payment/payout',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    /**
                     * 站内发送消息给用户
                     */
                    $notice = new notice;
                    $notice->uid = $uid;
                    $notice->add_time = time();
                    $notice->type = 5;
                    $notice->title = '提现发起';
                    $notice->money = $amount;
                    $notice->explain = '我们会在2个工作日内处理完毕。';
                    $notice->state = '提现申请已提交。';
                    $notice->save();
                    //写入order表
                    $order=new order();
                    $order->uid=$uid;
                    $order->amount=$amount;
                    $order->order_id=$order_id;
                    $order->o_type=0;
                    $order->add_time=time();
                    $order->status=0;
                    $order->save();
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
     * 取消出金
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function payment_withdraw_cancel(Request $request)
    {
    	$uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $order_no = $request->input('order_no');
        $order_no = $this->get_input($order_no);
        if(empty($uid) || empty($order_no)){
            return $this->jsonOut('2','缺少参数');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'payment_withdraw_cancel';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['order_no'] = $order_no;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('put','https://demo.tigerwit.com/api/third/payment/withdraw/cancel',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                    //删除order表里该条数
                    $order=new order();
                    $order->where('uid',$uid)->where('order_no',$order_no)->delete();
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
     * 出入金汇率
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function payment_rates(Request $request)
    {
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'payment_rates';
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/payment/rates',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('7',$res['message']);
        }
    }

    /**
     * 修改绑定银行卡信息
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function bind_bank_card(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $bank_name = $request->input('bank_name');
        $bank_name = $this->get_input($bank_name);
        $bank_addr = $request->input('bank_addr');
        $bank_addr = $this->get_input($bank_addr);
        $card_no = $request->input('card_no');
        $card_no = $this->get_input($card_no);
        $province = $request->input('province');
        $province = $this->get_input($province);
        $city = $request->input('city');
        $city = $this->get_input($city);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id！');
        }else if(empty($bank_name)){
            return $this->jsonOut('4','请输入银行名称');
        }else if(empty($bank_addr)){
            return $this->jsonOut('5','请输入开行地址');
        }else if(empty($card_no)){
            return $this->jsonOut('6','请输入银行卡号');
        }else if(strlen($card_no)<=16){
            return $this->jsonOut('10','银行卡号位数不对');
        }else if(empty($province)){
            return $this->jsonOut('8','请输入银行卡开户省份');
        }else if(empty($city)){
            return $this->jsonOut('9','请输入银行卡开户城市');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params = $request->except('uid');
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'payment_withdraw_cancel';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('put','https://demo.tigerwit.com/api/third/user/bank_card',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    return $this->jsonOut('1','绑定成功！');
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
     * 获取银行卡信息
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_bank_card(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id！');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'user_bank_card';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/user/bank_card',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    if(isset($res['data'])){
                        $data = $res['data'];
                        $tmp = bank::select('bank_img')->where('title',$data['bank_name'])->first();
                        $data['bank_img'] = config('app.domain').$tmp['bank_img'];
                        $data['card_no'] = '**** **** **** **** '.substr($data['card_no'],-4);
                        return $this->jsonOut('1','返回成功！',$data);
                    }else{
                        return $this->jsonOut('7',$res['message']);
                    }

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
     * 出金条件检查
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function payment_withdraw_limits(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id！');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'v2_payment_withdraw_limits';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign;
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/v2/payment/withdraw/limits',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $pams['private_key']    = config('app.private_key');
                    $pams['partner_key']    = config('app.partner_key');
                    $pams['partner_secret'] = config('app.partner_secret');
                    $pams['action']         = 'user_bank_card';
                    $pams['user_id']        = $uid;
                    $pams['mt4_id'] = $info->mt4_id;
                    $sign = $this->getSign($pams['private_key'], $pams); #生成签名
                    $pams['sign'] = $sign;
                    unset($pams['partner_key']); # 销毁加密key
                    unset($pams['partner_secret']); # 销毁加密key
                    $res1 = $this->curl_request('get','https://demo.tigerwit.com/api/third/user/bank_card',$params);
                    $res1 = json_decode($res1,true);

                    if($res1['is_succ']){
                        $data = $res['data'];
                        if($data['status']){
                            $data['bank_name'] = $res1['data']['bank_name'];
                            $data['bank_addr'] = $res1['data']['bank_addr'];
                            $data['card_no'] = $res1['data']['card_no'];
                            $data['province'] = $res1['data']['province'];
                            $data['city'] = $res1['data']['city'];
                            return $this->jsonOut('1','返回成功！',$data);
                        }else{
                            if(isset($res1['data'])){
                                return $this->jsonOut('7',$data['status_message']);
                            }
                            return $this->jsonOut('7',$res1['message']);
                        }

                    }else{
                        return $this->jsonOut('7',$res1['message']);
                    }
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
     * 获取汇率，余额，零钱包余额等信息
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_all_payment_info(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id！');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                //获取汇率信息
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'payment_rates';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/payment/rates',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data = $res['data'];
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
                //获取用户余额，零钱包余额等信息
                $pams['private_key']    = config('app.private_key');
                $pams['partner_key']    = config('app.partner_key');
                $pams['partner_secret'] = config('app.partner_secret');
                $pams['action']         = 'centre_asset';
                $pams['user_id']        = $uid;
                $pams['mt4_id'] = $info->mt4_id;
                $pams['type'] = 2;
                $sign = $this->getSign($pams['private_key'], $pams); #生成签名
                $pams['sign'] = $sign;
                unset($pams['partner_key']); # 销毁加密key
                unset($pams['partner_secret']); # 销毁加密key
                $res1 = $this->curl_request('get','https://demo.tigerwit.com/api/third/v2/centre/asset',$pams);
                $res1 = json_decode($res1,true);
                if($res['is_succ']){
                    $data['balance'] = $res1['data']['balance'];//余额
                    $data['wallet_balance'] =  $res1['data']['wallet_balance']; //零钱包余额
                    $webs = webconfig::find(1);
                    $data['web_hint'] = $webs->web_hint;
                    $data['web_ti'] = $webs->web_ti;
                    $data['in_money'] = $webs->in_money;
                    $data['out_money'] = $webs->out_money;
                    $data['in_money_max'] = $webs->in_money_max;
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