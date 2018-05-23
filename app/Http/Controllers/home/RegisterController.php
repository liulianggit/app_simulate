<?php

namespace App\Http\Controllers\home;

use App\Http\Controllers\common\CommonController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\aliyun\api_demo\SmsDemo;
use App\model\Users;
use App\model\old_user;
use App\model\userinfo;
use App\model\utmsource;
use App\model\user;
use App\model\msg;
use App\model\ips;


class RegisterController extends CommonController
{
    /**
     * 调用开户接口
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function open_user(Request $request){
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $data = []; //返回数据
        if(empty($uid)){
           return   $this->jsonOut('2','请传入uid');
        }
        $users = Users::find($uid);
        if(!empty($users)){
            $data['user_id'] = $uid;
            $data['phone'] = $users->utel;
            $data['private_key'] = config('app.private_key');
            $data['action'] = 'register';
            $re= $this->curl_request('get',"https://h5dev.open.tigerwit.com/third_napi");
            $re=json_decode($re,true);
            if($re['is_succ']=='true')
            {
                $path=$re['data']['register'];
            }else{
                return   $this->jsonOut('3','地址获取错误');
            }
            $data['path'] = $path;
            $params['private_key']    = config('app.private_key');
            $params['partner_key']    = config('app.partner_key');
            $params['partner_secret'] = config('app.partner_secret');
            $params['action']         = 'register';
            $params['user_id']        = $uid;
            $params['phone']        = $users->utel;
            $sign = $this->getSign($params['private_key'], $params); #生成签名
            $data['sign'] = $sign; 
            unset($params['partner_key']); # 销毁加密key
            unset($params['partner_secret']); # 销毁加密key

            return $this->jsonOut('1','返回成功',$data);
        }else{
            return $this->jsonOut('0','没有此用户！');
        }
    }
    //获取所有h5
    public function tiger_h5(Request $request){
      $re= $this->curl_request('get',"https://h5dev.open.tigerwit.com/third_napi");
        $re=json_decode($re,true);
        if($re['is_succ']=='true')
        {
         $path=$re['data']['evidence'];
         }
        var_dump($path);
    }
    /**
     * 发送短信验证码
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function sendmobile(Request $request)
    {   
        $phone=$request->input("utel");
        $type=$request->input("type");

        if(empty($phone)){
            return $this->jsonOut('2','手机号为空');
        }
        if(!preg_match("/^1[34578]\d{9}$/",$phone))
        {
           return $this->jsonOut('3','手机号格式不正确');
        }
        if($type=='1')
        {
          $users=new Users();
          $find=$users->where('utel',$phone)->first();
          if(!$find){
              return $this->jsonOut('5','该手机号尚未注册');
          }
        }
        //查询该手机号是否发送验证码
        $ips=new ips();
        $rs=$ips->where('phone',$phone)->orderBy('addtime','desc')->first();
        if($rs)
        {
            if((time()-$rs['addtime'])<60)
            {
                return $this->jsonOut('4','60秒内不能重复发送');
            }
        }
        //发送验证码
        set_time_limit(0);
        header('Content-Type: text/plain; charset=utf-8');
        if($type=='1'){
            $res = SmsDemo::sendSms2($phone);
        }else {
            $res = SmsDemo::sendSms($phone);
        }
        if($res->Message == 'OK'){//发送成功成功后写入ips表
            $ips = new ips();
            $ips->ip = $this->getIp();
            $ips->addtime = time();
            $ips->phone=$phone;
            $ips->code=session('code');
            $ips->save();
            return $this->jsonOut('1','发送成功');
        }else{
           return $this->jsonOut('0','发送失败请联系客服');
        }

    }

    /* 手机号注册+登录
    * @param  Request $request [description]
    * @return [type]           [description]
    */
    public function codeLogin(Request $request)
    {
        $phone=$request->input("utel");
        if(empty($phone)){
            return $this->jsonOut('2','手机号为空');
        }
        if(!preg_match("/^1[34578]\d{9}$/",$phone))
        {
            return $this->jsonOut('4','手机号格式不正确');
        }
        $code=$request->input('code');
        if(empty($code)){
            return $this->jsonOut('3','验证码为空');
        }
        $device_token = $request->input('device_token','');
        $ips= new ips();
        $rs=$ips->where('phone',$phone)->where('code',$code)->first();//查询验证码是否发送
        if($rs) {//已经发送验证码
            if((time()-$rs->addtime)>60)
                {
                    return $this->jsonOut('5','验证码超时');
                }
            //判断用户是否已注册
            $user = new Users();
            $res = $user->where('utel', $phone)->first();
            if (!$res) {//没有注册过
                //写入注册表
                $users=new Users();
                $old_user = old_user::where('utel',$phone)->first();
                // $city = $this->getIpLookup($ip);
                $utm_source = $request->input('utm_source', '');
                if (strlen($request->input('reurl')) > 200) {
                    $reurl = $request->input('reurl') . substr(0, 200);
                } else {
                    $reurl = $request->input('reurl', '');
                }

                $users->ptype = $this->getResource($utm_source);
                $users->uname = '';
                $users->utel = $phone;
                $users->pwd = '';
                $users->telcity = $this->getTelCity($phone);
                $users->weixin = '';
                $users->note =  $request->input('note', '');
                $users->utm_source = $utm_source;
                $users->utm_term = $request->input('utm_term', '');
                $users->ingod = 0;
                $users->sumgod = 0;
                $users->contactnum = 0;
                if($old_user){
                    $users->contactpeople = $old_user['contactpeople'];
                }else{
                    $users->contactpeople = $this->getNameByTypeId($this->getResource($utm_source));
                }
                $users->show = 0;
                $users->show1 = 1;
                $users->jemail = '';
                $users->utm_medium = $request->input('utm_medium', '');
                $users->utm_content =$request->input('utm_content', '');
                $users->utm_campaign =$request->input('utm_campaign', '');
                $users->AddDate = date('Y-m-d H:i:s',time());
                $users->regsource = 1;
                $users->ucheck = 1;
                $users->IP =  $this->getIp();
                $users->url = $request->input('url', '');
                $users->reurl = $reurl;
                $users->str1 = $this->getAgent();
                $users->save();
                //写入消息表
                $msg = new msg();
                $msg->uid = 1;
                $msg->uname = '系统管理员';
                $msg->addtime = date('Y-m-d H:i:s',time());
                $msg->content = '有新的客户分配到您的名下，请查看！';
                $msg->status = 1;
                $msg->type = '系统消息';
                $msg->juid = $this->getResource($utm_source);
                $msg->save();
                //返回用户信息
                $re=$users->where('utel',$phone)->first();
                $data=[];
                if($re){
                 $data['uid']=$re->Id;
                 $data['utel']=$re->utel;
                    if($re->pwd){
                        $data['pwd_status']='1';
                    }else{
                        $data['pwd_status']='0';
                    }
                }
                $data['is_true'] = '0';
                //写入userinfo表
                $userinfo = new userinfo;
                $userinfo->uid=$re->Id;
                $userinfo->is_true=0;
                $userinfo->uname=$re->uname;
                $userinfo->utel=$re->utel;
                if($device_token){
                    $userinfo->device_token = $device_token;
                }
                $userinfo->save();
                $userId = $re['Id'];
                $name = $utel;
                $portraitUri = config('domain').'/upload/pic/20180502100837432421816.jpg';
                $params = ['userId'=>$userId,'name'=>$name,'portraitUri'=>$portraitUri];
                $rongyun_token = $this->send_curl('/user/getToken.json',$params,'urlencoded','im','POST');
                $rongyun_token = json_decode($rongyun_token,true);
                $data['rongyun_token'] = $rongyun_token['token'];
                return $this->jsonOut('1','登陆成功',$data);
            }else{//已经注册过，直接返回登陆成功
                //返回用户信息
                $users=new Users();
                $re=$users->where('utel',$phone)->first();
                $data=[];
                if($re){
                    $data['uid']=$re->Id;
                    $data['utel']=$re->utel;
                    if($re->pwd){
                        $data['pwd_status']='1';
                    }else{
                        $data['pwd_status']='0';
                    }
                }
                $userinfo = new userinfo;
                $info = $userinfo->where('uid',$res['Id'])->first();
                if($device_token){
                    $info->device_token = $device_token;
                    $info->save();
                }
                $data['is_true'] = $info['is_true']?strval($info['is_true']):'0';
                $userId = $re['Id'];
                $name = $phone;
                $portraitUri = config('domain').'/upload/pic/20180502100837432421816.jpg';
                $params = ['userId'=>$userId,'name'=>$name,'portraitUri'=>$portraitUri];
                $rongyun_token = $this->send_curl('/user/getToken.json',$params,'urlencoded','im','POST');
                $rongyun_token = json_decode($rongyun_token,true);
                $data['rongyun_token'] = $rongyun_token['token'];
                return $this->jsonOut('1','登陆成功',$data);
            }
        }else{
            return $this->jsonOut('0','验证码错误');
        }
    }

    /**
     * 获取客户端ip
     * @return [type] [description]
     */
    public function getIp(){  
        $realip = '';  
        $unknown = 'unknown';  
        if (isset($_SERVER)){  
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)){  
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);  
                foreach($arr as $ip){  
                    $ip = trim($ip);  
                    if ($ip != 'unknown'){  
                        $realip = $ip;  
                        break;  
                    }  
                }  
            }else if(isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP']) && strcasecmp($_SERVER['HTTP_CLIENT_IP'], $unknown)){  
                $realip = $_SERVER['HTTP_CLIENT_IP'];  
            }else if(isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)){  
                $realip = $_SERVER['REMOTE_ADDR'];  
            }else{  
                $realip = $unknown;  
            }  
        }else{  
            if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)){  
                $realip = getenv("HTTP_X_FORWARDED_FOR");  
            }else if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)){  
                $realip = getenv("HTTP_CLIENT_IP");  
            }else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)){  
                $realip = getenv("REMOTE_ADDR");  
            }else{  
                $realip = $unknown;  
            }  
        }  
        $realip = preg_match("/[\d\.]{7,15}/", $realip, $matches) ? $matches[0] : $unknown;  
        return $realip;  
    }  
  
    /**
     * 获取IP归属地
     * @param  string $ip [description]
     * @return [type]     [description]
     */
    public function getIpLookup($ip = ''){  
        if(empty($ip)){  
            $ip = $this->getIp();  
        }  
        $res = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js&ip=' . $ip);  
        if(empty($res)){ return false; }  
        $jsonMatches = array();  
        preg_match('#\{.+?\}#', $res, $jsonMatches);  
        if(!isset($jsonMatches[0])){ return false; }  
        $json = json_decode($jsonMatches[0], true);  
        if(isset($json['ret']) && $json['ret'] == 1){  
            $json['ip'] = $ip;  
            unset($json['ret']);  
        }else{  
            return false;  
        }  
        return $json['city'];  
    } 

    /**
     * 获取手机号归属地
     * @param  string $phone [description]
     * @return [type]        [description]
     */
    public function getTelCity($phone)
    {
        header("content-type:text/html;charset=utf-8");
        $res = @file_get_contents('https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$phone);
        $res = trim(iconv("gb2312", "utf-8//IGNORE",$res));
        $data = explode(',',$res);
        $telcity = str_replace('\'','',explode(':',$data[1])[1]);
        return $telcity;
    }

    /**
     * 获取用户访问设备
     * @return [type] [description]
     */
    public function getAgent()
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $_type = '';
        if(stripos($user_agent,'windows')){
            $_type = 'windows';
        }
        else if (stripos($user_agent,'macintosh'))
        {
            $_type = "mac";
        }
        else if (stripos($user_agent,'iphone'))
        {
            $_type = "iPhone";
        }
        else if (stripos($user_agent,'windows phone'))
        {
            $_type = "Windows Phone";
        }
        else if (stripos($user_agent,'ipad'))
        {
            $_type = "iPad";
        }
        else if (stripos($user_agent,'android'))
        {
            $_type = "Android";
        }
        else
        {
            $_type = "android";
        }
        return $_type;
    }

    /**
     * 分配来源给有此权限的销售最少人数的那个销售（当天的）
     * @param  string $utm_source [description]
     * @return [type]             [description]
     */
    public function getResource($utm_source)
    {
        $source = new utmsource;
        $result = $source->where('title',$utm_source)->first(); //获取来源id
        $admin = new user;
        $data = null;
        if($result){
            $data = $admin->where('source','like','%,'.$result->id.',%')->where('status',1)->select('id')->get();   //获取有此来源的销售人员id列表
        }
        $users = new Users;
        $resource = 0;
        $res = [];
        if(count($data)>0){
            foreach ($data as $k => $v) {
                $num = $users->where('ptype',$v['id'])->where('AddDate','like','%'.date('Y-m-d',time()).'%')->count();
		          $res[$v['id']] = $num;
            }
             return array_search(min($res),$res);    //返回数量最少的销售人员id
             // dd(array_search(min($res),$res));
        }else{
            return 1;   //没有此来源默认为1
            // echo 1;
        }
       
    }

    /**
     * 根据id获取管理员名称
     * @param  [type] $_ptype [description]
     * @return [type]         [description]
     */
    public function getNameByTypeId($_ptype)
    {
        $admin = new user;
        $result = $admin->find($_ptype);
        if($result){
            return $result->username;
        }else{
            return '';
        }
    }

}
