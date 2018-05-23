<?php

namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\Users;
use App\model\userinfo;
use App\model\ips;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\common\CommonController;
use Log;


class LoginController extends CommonController
{

    /**
     * 调用修改mt4密码接口
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function open_pass(Request $request)
    {
        $data = [];
        $pwd = $request->input('pwd');
        $pwd = $this->get_input($pwd);
        $rpwd = $request->input('rpwd');
        $rpwd = $this->get_input($rpwd);
        $uid = $request->input('uid');
        if(empty($pwd) || empty($rpwd)){
            return $this->jsonOut('5','密码不能为空！');
        }else if($pwd != $rpwd)
        {
            return $this->jsonOut('4','两次密码不一致！');
        }else if(strlen($pwd) < 6 || strlen($pwd) >15)
        {
            return $this->jsonOut('3','请输入6到15位之间的密码！');
        }else if(!preg_match('/^(?![A-Z]+$)(?![a-z]+$)(?!\d+$)(?![\W_]+$)\S{6,15}$/', $pwd)){
            return $this->jsonOut('7','请输入包含数字、小写字母、大写字母、特殊符号中两种及以上的密码！');
        }else if(empty($uid))
        {
            return $this->jsonOut('6','请传入用户id！');
        }
        $users = userinfo::where('uid',$uid)->first();
        if(!empty($users)){
            $params['private_key']    = config('app.private_key');
            $params['partner_key']    = config('app.partner_key');
            $params['partner_secret'] = config('app.partner_secret');
            $params['action']         = 'user_password';
            $params['user_id']        = $uid;
            $params['mt4_id'] = $users->mt4_id;
            $params['new_passwd'] = $pwd;
            $params['confirm_passwd'] = $rpwd;
            $sign = $this->getSign($params['private_key'], $params); #生成签名
            $params['sign'] = $sign; 
            unset($params['partner_key']); # 销毁加密key
            unset($params['partner_secret']); # 销毁加密key
            $res = $this->curl_request('put','https://demo.tigerwit.com/api/third/user/passwd',$params);
            $res = json_decode($res,true);
            if($res['is_succ']){
                $users->password = $pwd;
                if($users->save()){
                    return $this->jsonOut('1','密码修改成功！');
                }else{
                    return $this->jsonOut('0','密码修改失败！');
                }
            }else{
                return $this->jsonOut('0',$res['message']);
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }

    }
	/**
	 * 用户登录
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function index(Request $request)
	{
		$utel = $request->input('utel');//手机号
        $utel = $this->get_input($utel);
		$pwd = $request->input('pwd');//密码
        $pwd = $this->get_input($pwd);
        $device_token = $request->input('device_token','');
        if(empty($utel) || empty($pwd)){
            return $this->jsonOut('4','手机号或密码不能为空！');
        }
        if(!preg_match("/^1[34578]\d{9}$/",$utel)){
            return $this->jsonOut('5','手机号格式不正确！');
        }
		$users = new Users;
		$res = $users->where('utel',$utel)->first();
        $data = [];
        $data['uid'] = $res['Id'];
		if($res){
			$check_pwd = $this->get_pwd($pwd,$res->Id);
            // dd($check_pwd);
			if($check_pwd == $res->pwd){
                $userinfo = new userinfo;
                $info = $userinfo->where('uid',$res['Id'])->first();
                if($info){
                    if($device_token){
                        $info->device_token = $device_token;
                        $info->save();
                    }
                    $data['is_true'] = $info['is_true']?strval($info['is_true']):'0';
                    $data['pwd_status'] = '1';    //1表示不用设置密码
                    $data['utel'] = $utel;
                    $userId = $res['Id'];
                    $name = $utel;
                    $portraitUri = config('domain').'/upload/pic/20180502100837432421816.jpg';
                    $params = ['userId'=>$userId,'name'=>$name,'portraitUri'=>$portraitUri];
                    $rongyun_token = $this->send_curl('/user/getToken.json',$params,'urlencoded','im','POST');
                    $rongyun_token = json_decode($rongyun_token,true);
                    $data['rongyun_token'] = $rongyun_token['token'];
                    return $this->jsonOut('1','登录成功！',$data);
                }else{
                    return $this->jsonOut('2','无此用户详情');
                }
                
			}else{
				return $this->jsonOut('2','密码错误！');
			}
		}else{
            return $this->jsonOut('3','用户不存在！');
        }

	}

    /**
     * 设置密码
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function up_pwd(Request $request)
    {
        $pwd = $request->input('pwd');
        $pwd = $this->get_input($pwd);
        $rpwd = $request->input('rpwd');
        $rpwd = $this->get_input($rpwd);
        $id = $request->input('uid');
        if(empty($pwd) || empty($rpwd)){
            return $this->jsonOut('5','密码不能为空！');
        }else if($pwd != $rpwd)
        {
            return $this->jsonOut('4','两次密码不一致！');
        }else if(strlen($pwd) < 8 || strlen($pwd) >16)
        {
            return $this->jsonOut('3','请输入8到16位之间的密码！');
        }else if(empty($id))
        {
            return $this->jsonOut('6','请传入用户id！');
        }

        $users = Users::find($id);
        if($users){
            $res = DB::table('xy_app_users')->where('Id',$id)->update(['pwd'=>$this->get_pwd($pwd,$id)]);
            // $users->pwd = $pwd;
            // $res = $users->save();
            if($res){
                return $this->jsonOut('1','密码设置成功！');
            }else{
                return $this->jsonOut('0','密码设置失败！');
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }

    /**
     * 找回密码（忘记密码）
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function lose_pwd(Request $request)
    {
        $phone = $request->input("utel");
        $phone = $this->get_input($phone);
        $pwd = $request->input('pwd');
        $pwd = $this->get_input($pwd);
        $rpwd = $request->input('rpwd');
        $rpwd = $this->get_input($rpwd);
        if(empty($phone)){
            return $this->jsonOut('2','手机号不能为空！');
        }else if(empty($pwd)){
            return $this->jsonOut('4','用户密码不能为空！');
        }else if(empty($rpwd)){
            return $this->jsonOut('5','用户确认密码不能为空！');
        }else if(!preg_match("/^1[34578]\d{9}$/",$phone)){
            return $this->jsonOut('6','手机号格式不正确！');
        }else if($pwd != $rpwd){
            return $this->jsonOut('7','两次密码输入不一致！');
        }
        
        //判断此用户是否存在
        $users = new Users;
        $res = $users->where('utel',$phone)->first();
        if($res){
            $result = DB::table('xy_app_users')->where('Id',$res['Id'])->update(['pwd'=>$this->get_pwd($pwd,$res['Id'])]);
            if($result){
                return $this->jsonOut('1','密码设置成功！');
            }else{
                return $this->jsonOut('9','密码设置失败！');
            }
        }else{
            return $this->jsonOut('10','此用户不存在！');
        }
        
    }

    /**
     * 找回密码验证手机验证码
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_code(Request $request){
        $phone = $request->input("utel");
        $phone = $this->get_input($phone);
        $code = $request->input('code');
        $code = $this->get_input($code);
        if(empty($phone)){
            return $this->jsonOut('2','手机号不能为空！');
        }else if(empty($code)){
            return $this->jsonOut('3','验证码不能为空！');
        }else if(!preg_match("/^1[34578]\d{9}$/",$phone)){
            return $this->jsonOut('6','手机号格式不正确！');
        }

        $ips= new ips();
        $rs=$ips->where('phone',$phone)->where('code',$code)->first();//查询验证码是否发送
        if($rs) {//已经发送验证码
            if((time()-$rs->addtime)>60)
            {
                return $this->jsonOut('8','验证码超时');
            }else{
                $data['utel'] = $phone;
                return $this->jsonOut('1','验证码正确',$data);
            }
            
        }else{
            return $this->jsonOut('0','验证码输入错误！');
        }
    }

	/**
	 * 密码加密
	 * @param  [type] $pwd [description]
	 * @return [type]      [description]
	 */
	public function get_pwd($pwd,$uid){
		$new_pwd = md5(md5(strrev($pwd.$uid)));
		return $new_pwd;
	}
    
}
