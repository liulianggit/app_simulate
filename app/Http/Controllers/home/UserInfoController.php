<?php

namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use Illuminate\Http\Concerns\InteractsWithInput;
use App\Http\Controllers\Controller;
use App\model\Users;
use App\model\userinfo;
use App\model\webconfig;
use App\model\order;
use App\model\trade;
use App\model\notice;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\common\CommonController;
use Log;
use App\upload\Upload;

class UserInfoController extends CommonController
{

	/**
	 * 公共回调接口
	 * @return [type] [description]
	 */
    public function comm_back(Request $request){
        $data = $request->all();
        if($data['function'] == 'AUDIT'){
            $result = $this->open_lose($data);
            Log::info("audit come in. ".$result);
            return $result;
        }else if($data['function'] == 'REGISTER'){
            return $this->open_success($data);
        }else if($data['function'] == 'PAYEVIDENCE')
        {
        	return $this->evidence_back($data);
        }else if($data['function'] == 'WITHDRAW'){//出金
            return $this->withdraw_back($data);
        }else if($data['function'] == 'DEPOSIT'){//入金
            $rs = $this->deposit_back($data);
            Log::info("audit come in. ".$rs);
            return $rs;
        }else if($data['function'] == 'CLOSE_TRADE'){
            return $this->trade_back($data);
        }
    }
	/**
	 * 获取用户头像和用户昵称信息
	 * @return [type] [description]
	 */
    public function index(Request $request)
    {
    	$uid = $request->input('uid');
    	$uid = $this->get_input($uid);
    	if(empty($uid)){
    		return $this->jsonOut('2','请传入用户id！');
    	}
    	$userinfo = new userinfo;
    	$pic = $userinfo->where('uid',$uid)->first();
    	$data = [];
		$webconfig = webconfig::find(1);
		$users= new Users();
    	if($pic){
			if($pic->u_pic) {
				$data['u_pic'] = config('app.domain').$pic['u_pic'];
			}else{
				$data['u_pic'] = config('app.domain').$webconfig['web_pic'];
			}
			if($pic->nickname) {
				$data['nickname'] = $pic['nickname'];
			}else{
				$info=$users->where('id',$uid)->first();
				$data['nickname'] = "用户".$info->utel;
			}
    		return $this->jsonOut('1','获取成功！',$data);
    	}else{
			$data['u_pic'] = config('app.domain').$webconfig['web_pic'];
			$info=$users->where('id',$uid)->first();
    		$data['nickname'] = "用户".$info->utel;
    		return $this->jsonOut('1','获取成功！',$data);
    	}
    }
	//修改头像
	public function upload_pic(Request $request)
	{
		//获取文件对象
		$file = preg_replace("/\s/",'+',$request->input('u_pic'));
		//获取uid
		$uid = $request->input('uid');
		$uid = $this->get_input($uid);
		if(empty($file)){
			return $this->jsonOut('2','请选择上传图片');
		}else if(empty($uid)){
			return $this->jsonOut('3','请传入用户id！');
		}
        
        $newName = date('YmdHis').mt_rand(1000,9999).$uid.'.jpg';//新文件名
        $new_file = public_path().'/upload/pic/'.$newName;//目录
        $filepath = '/upload/pic/'.$newName;//图片路径
        if(file_put_contents($new_file, base64_decode($file))){
            //以下是图片路劲在数据库里的处理
            $userinfo = new userinfo;
            $res = $userinfo->where('uid',$uid)->first();
            if($res){
                $res->u_pic = $filepath;
                if($res->save()){
                    $data['u_pic'] = config('app.domain').$filepath;
                    return $this->jsonOut('1','修改成功！',$data);
                }else{
                    return $this->jsonOut('0','修改失败，请重试！');
                }
            }else{
                $userinfo->uid = $uid;
                $userinfo->u_pic = $filepath;
                if($userinfo->save()){
                    $data['u_pic'] = config('app.domain').$filepath;
                    return $this->jsonOut('1','修改成功！',$data);
                }else{
                    return $this->jsonOut('0','修改失败，请重试！');
                }
            }
        }else{
            return $this->jsonOut('0','修改失败，请重试！');
        }
		
	}
	/**
     * 修改用户昵称
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function up_nickname(Request $request)
    {
    	$uid = $request->input('uid');
    	$uid = $this->get_input($uid);
    	$nickname = $request->input('nickname');
    	$nickname = $this->get_input($nickname);
    	if(empty($uid)){
    		return $this->jsonOut('2','请传入用户id！');
    	}else if(empty($nickname)){
    		return $this->jsonOut('3','请输入用户昵称！');
    	}
    	$userinfo = new userinfo;
    	$res = $userinfo->where('uid',$uid)->first();
    	if($res){
    		$res->nickname = $nickname;
    		if($res->save()){
    			$data['nickname'] = $nickname;
    			return $this->jsonOut('1','修改成功！',$data);
    		}else{
    			return $this->jsonOut('0','修改失败，请重试！');
    		}
    	}else{
    		$userinfo->uid = $uid;
    		$userinfo->nickname = $nickname;
    		if($userinfo->save()){
    			$data['nickname'] = $nickname;
    			return $this->jsonOut('1','修改成功！',$data);
    		}else{
    			return $this->jsonOut('0','修改失败，请重试！');
    		}
    	}
    }

    /**
     * 修改绑定手机号
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function up_phcode(Request $request)
    {
    	$uid = $request->input('uid');
    	$phcode = $request->input('utel');
    	$uid = $this->get_input($uid);
    	$phcode = $this->get_input($phcode);
    	if(empty($uid)){
    		return $this->jsonOut('2','请传入用户id！');
    	}else if(empty($phcode)){
    		return $this->jsonOut('3','手机号不能为空！');
    	}else if(!preg_match("/^1[34578]\d{9}$/",$phcode)){
    		return $this->jsonOut('4','请输入正确格式的手机号！');
    	}

    	$users = new Users;
    	$res = $users->find($uid);
    	if($res){
    		$res->utel = $phcode;
    		if($res->save()){
    			$data['utel'] = $phcode;
    			return $this->jsonOut('1','修改成功！',$data);
    		}else{
    			return $this->jsonOut('0','修改失败，请重试！');
    		}
    	}else{
    		return $this->jsonOut('0','修改失败，请重试！');
    	}

    }

    /**
     * 获取充值提示
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_hint(Request $request){
    	$result = webconfig::find(1);
    	$data = [];
    	if($result){
    		$data['web_hint'] = $result['web_hint'];
    		return $this->jsonOut(1,'获取成功',$data);
    	}
    	return $this->jsonOut(0,'获取失败');
    }

    /**
     * 开户成功回调
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    private function open_success($data)
    {
    	$signature = $data['signature'];
        unset($data['signature']);
        $params = $data;
    	$params['private_key'] = config('app.private_key');
		$params['partner_key'] = config('app.partner_key');
		$params['partner_secret'] = config('app.partner_secret');
		$params['function'] = 'REGISTER';
		$sign = $this->getSign($params['private_key'], $params); #生成签名
		if($sign == $signature){
			$userinfo = new userinfo;
			$rs = $userinfo->where('uid',$data['user_id'])->first();
			if($rs){
				$rs->certificate_no = $data['certificate_no'];
				$rs->real_name = $data['real_name'];
				$rs->mt4_id = $data['mt4_id'];
				$rs->demo_mt4 = $data['demo_mt4'];
				$rs->register_time = $data['register_time'];
				$rs->apply_time = $data['apply_time'];
				$rs->password = $data['password'];
				$rs->is_true = 2;	//开户后开通体验金账户
				$rs->is_check = 2;	//开户后状态为通过审核
				if($rs->save()){
					DB::table('xy_app_users')->where('Id',$data['user_id'])->update(['show2'=>1]);
				}
				
				return $this->jsonOut('success','回调成功');
			}else{
				$userinfo->uid = $data['user_id'];
				$userinfo->certificate_no = $data['certificate_no'];
				$userinfo->real_name = $data['real_name'];
				$userinfo->mt4_id = $data['mt4_id'];
				$userinfo->demo_mt4 = $data['demo_mt4'];
				$userinfo->register_time = $data['register_time'];
				$userinfo->apply_time = $data['apply_time'];
				$userinfo->password = $data['password'];
				$userinfo->is_true = 2;
				$userinfo->is_check = 2;	//开户后状态为通过审核
				if($userinfo->save()){
					DB::table('xy_app_users')->where('Id',$data['user_id'])->update(['show2'=>1]);
				}
				return $this->jsonOut('success','回调成功');
			}
			
		}else{
			return $this->jsonOut('fail','签名验证失败');
		}
    }

    /**
     * 开户审核失败回调
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    private function open_lose($data)
    {
        $signature = $data['signature'];
        unset($data['signature']);
        $params = $data;
    	$params['private_key'] = config('app.private_key');
        $params['partner_key'] = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        Log::info("open_lose:", $params);
		$sign = $this->getSign($params['private_key'], $params); #生成签名
		if($sign == $signature){
			$userinfo = new userinfo;
			$rs = $userinfo->where('uid',$data['user_id'])->first();
			if($rs){
				$rs->is_check = 3;	//拒绝审核
				$rs->save();
				
				return $this->jsonOut('success','回调成功');
			}else{
                $userinfo->uid = $data['user_id'];
				$userinfo->is_check = 3;	//拒绝审核
				$userinfo->save();
				return $this->jsonOut('success','回调成功');
			}
		}else{
			return $this->jsonOut('fail','签名验证失败');
		}
        Log::info("end");
    }
    
    /**
     * 大额入金审核回调
     * @param  Request $request [description]
     * @return [type]           [description]
     */
	public function evidence_back($data)
    {
    	$signature = $data['signature'];
        unset($data['signature']);
        $params = $data;
    	$params['private_key'] = config('app.private_key');
        $params['partner_key'] = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
		$sign = $this->getSign($params['private_key'], $params); #生成签名
		if($sign == $signature){
			/**
			 * 结果发站内消息给用户
			 */
			return $this->jsonOut('success','回调成功');
		}else{
			return $this->jsonOut('fail','签名验证失败');
		}
    }

    /**
     * 出金回调
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function withdraw_back($data)
    {
        $signature = $data['signature'];
        unset($data['signature']);
        $params = $data;
        $params['private_key'] = config('app.private_key');
        $params['partner_key'] = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        if($sign == $signature){
            $order = order::where('order_id',$data['order_no'])->first();
            if($data['trade_status'] == 'SUCCESS'){
                $order->status = 1;
            }else{
                $order->status = 2;
            }
            $order->save();
            
            //站内发送消息给用户
            $notice = new notice;
            $notice->uid = $order->uid;
			$notice->add_time = time();
            $notice->type = 4;
            $notice->title = '交易账户提现申请通过';
            $notice->money = $order->amount;
            $notice->explain = '资金将在1-2个工作日内到达提现银行账户，如有疑问请联系PandaFx熊猫外汇客服。';
            $notice->save();
            return $this->jsonOut('success','回调成功');
        }else{
            return $this->jsonOut('fail','签名验证失败');
        }
    }

    /**
     * 入金回调
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function deposit_back($data)
    {
        $signature = $data['signature'];
        unset($data['signature']);
        $params = $data;
        $params['private_key'] = config('app.private_key');
        $params['partner_key'] = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        if($sign == $signature){
            $order = order::where('order_id',$data['order_no'])->first();
            if($data['trade_status'] == 'SUCCESS'){
                $order->status = 1;
            }else{
                $order->status = 2;
            }
            $order->save();
            //站内发送消息给用户
            $notice = new notice;
            $notice->uid = $order->uid;
            $notice->add_time = time();
            $notice->type = 5;
            $notice->title = '交易账户入金申请通过';
            $notice->money = $order->amount;
            $notice->explain = '资金将在1-2个工作日内到达账户，如有疑问请联系PandaFx熊猫外汇客服。';
            $notice->save();
            return $this->jsonOut('success','回调成功');
        }else{
            return $this->jsonOut('fail','签名验证失败');
        }
    }

    /**
     * 订单成交回调
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function trade_back($data)
    {
        $signature = $data['signature'];
        unset($data['signature']);
        $params = $data;
        $params['private_key'] = config('app.private_key');
        $params['partner_key'] = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        if($sign == $signature){
            $trade = trade::where('ticket',$params['ticket'])->first();
            $trade->t_type = $params['type'];
            $trade->amount = isset($params['amount'])?$params['amount']:'';
            $trade->save();
            /**
             * 站内发送消息给用户
             */
			//交易类型：挂单成交 PENDING_ACTIVATION，止盈成交 TAKE_PROFIT，止损成交 STOP_LOSS，爆仓 STOPOUTS
            $notice = new notice;
            $notice->uid =  $data['user_id'];
			$notice->add_time = time();
            if($data['type'] == 'PENDING_ACTIVATION'){//挂单成交
                $notice->type = 2;
                if($data['cmd'] == 0){
                    $notice->title = '挂单买涨成交';
                    $notice->order_dircet = '买涨';

                   }else{
                    $notice->title = '挂单买跌成交';
                    $notice->order_dircet = '买跌';
                }
				$notice->trade_type = $data['symbol'];
				$notice->content = '您的挂单交易已成交';
                $notice->money = $trade->price_record;
                $notice->order_num = $trade->volume;
				//极光推送
				$content="您的".$data['symbol']."已经挂单成交，成交价格$".$trade->price_record;
				$this->send_push($data['user_id'],$content);
            }else if($data['type'] == 'TAKE_PROFIT'){//止盈成交
				$notice->type = 1;
				$notice->title = '止盈平仓通知';
				$notice->money = $trade->price_record;
				$notice->order_no = $data['ticket'];
				$notice->trade_type =  $data['symbol'];
				//极光推送
				$content="您的".$data['symbol']."已经止盈平仓,盈利$".$trade->amount;
				$this->send_push($data['user_id'],$content);
			}else if($data['type'] == 'STOP_LOSS'){//止损成交
				$notice->type = 1;
				$notice->title = '止损平仓通知';
				$notice->money = $trade->price_record;
				$notice->order_no = $data['ticket'];
				$notice->trade_type =  $data['symbol'];
				//极光推送
				$content="您的".$data['symbol']."已经止损平仓,亏损$".abs($trade->amount);
				$this->send_push($data['user_id'],$content);
			}else if($data['type'] == 'STOPOUTS'){
				$notice->type = 3;
				$notice->title = '订单强平通知';
				$notice->content = '保障金不足订单已被强平';
				$notice->order_no = $data['ticket'];
				$notice->trade_type =  $data['symbol'];
				if($data['cmd'] == 0){
					$notice->oder_dircet = '买涨';
				}else{
					$notice->oder_dircet = '买跌';
				}

			}
            $notice->save();
            return $this->jsonOut('success','回调成功');
        }else{
            return $this->jsonOut('fail','签名验证失败');
        }
    }
     
}
