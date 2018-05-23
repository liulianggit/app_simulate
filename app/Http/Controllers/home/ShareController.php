<?php

namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\bander;
use App\model\cash;
use App\model\cashlog;
use App\model\deal;
use App\model\rebate;
use App\model\msg;
use App\model\Users;
use App\model\user;
use App\model\ips;
use App\model\top;
use App\model\userinfo;
use App\aliyun\api_demo\SmsDemo;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Crypt;
use App\Http\Controllers\common\CommonController;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class ShareController extends CommonController
{
    /**
     * 发送短信验证码
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function sendphcode(Request $request)
    {   
        $phone=$request->input("phone");
        $bid=$request->input("bid");
        if(empty($phone)){
            return $this->jsonOut('2','手机号为空');
        }else if(empty($bid)){
            return $this->jsonOut('4','邀请链接已失效',$bid);
        }
        if(!preg_match("/^1[34578]\d{9}$/",$phone))
        {
           return $this->jsonOut('3','手机号格式不正确');
        }
        $bid = decrypt($bid);
        $rs = Users::where('utel',$bid)->first();
        if(!$rs){
            return $this->jsonOut('4','邀请链接已失效',$bid);
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
        $res = SmsDemo::sendSms($phone);//此处需要改模板
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

	/**
	 * 获取用户信息，加载邀请页面
	 * @return [type] [description]
	 */
    public function index(Request $request)
    {
    	$bander = new bander;
        $pid = $request->input('pid','');
        if($pid==''){
            echo '<script>alert("请传入用户id");</script>';
            exit;
        }
        $userinfo = userinfo::where('uid',$pid)->first();
        if(!$userinfo){
            echo '<script>alert("没有此用户");</script>';
            exit;
        }else{
           if($userinfo->is_true ==0){
                echo '<script>alert("请先开户");</script>';
                exit;
            } 
        }

        $result = $bander->where('pid',$pid)->get();  //获取该用户的子用户
        //拼装信息
        $data = [];
        $data['users'] = [];
        if(count($result)>0){
            $data['num'] = count($result);
            $data['cumulate'] = 0;
            //给用户手机号加密
            foreach ($result as $k =>$v) {
                $data['users'][$k] = substr($v['bid'], 0,3).'****'.substr($v['bid'], -4);
                $data['records'][$k] = $this->getRecordById($v['bid']);   //获取子用户昨日交易产生的佣金
                // $data['cumulate'] += $data['records'][$k];
            }
            $cash = cash::where('uid',$pid)->first();
            $data['cumulate'] = $cash->cumulate;//累积获得佣金
            $data['freeze']=$cash->freeze;//提现中的佣金
            $data['balance'] = $cash->balance; //可提现金额
        }else{
            $data['num'] = 0;
            $data['cumulate'] = 0;
            $data['balance'] = 0; //可提现金额
            $data['freeze'] = 0;   //提现中的金额
        }
        
        return view('share.index',['data'=>$data,'uid'=>$pid]);
    	
    }

    /**
     * 根据id获取该用户总交易手数
     */
    public function getRecordById($bid)
    {
        //获取昨天早上5点的时间戳
        $start_time = mktime(5,0,0,date('m'),date('d')-1,date('Y')); 
        //获取今日5点的时间戳
        $end_time = mktime(5,0,0,date('m'),date('d'),date('Y')); 
        $deal = new deal;
        $record = 0;    //累计返佣
        $result = $deal->where('uid',$bid)->whereBetween('add_time',[$start_time,$end_time])->get();//获取此用户昨日交易列表

        if(!$result || count($result)==0){
            return $record;
        }else{
            foreach ($result as $k => $v) {
                $record += $v['wai']*2 + $v['gold']*3 + $v['cfd']*1 + $v['yin']*10 + $v['dollar']*0.2 + $v['energy']*2;
            }
        }
        return $record;
    }

    /**
     * 提现操作
     * @param  [type] $id [用户id]
     * @return [type]     [description]
     */
    public function getMoney(Request $request)
    {   
        $id = $request->input('id');
        $cash = new cash;
        $res = $cash->where('uid',$id)->first();    //获取用户金额信息

        $data['status'] = 0;
        $data['msg'] = "操作失败，请联系客服！";
        if(Input::get('money')=='' || is_null(Input::get('money')) || Input::get('money')<50){
            $data['msg'] = '请输入金额大于50的数字';
            return json_encode($data);
        }
        $userinfo = userinfo::where('uid',$id)->first();
        if($res && $userinfo){
            if($res->balance >= Input::get('money')){   //可提现金额大于等于提现金额
                $cashlog = new cashlog;
                $cashlog->uid = $id;
                $cashlog->money = Input::get('money');
                $cashlog->cashtime = time();
                $cashlog->status = 0;
                $cashlog->u_name = $userinfo->real_name;
                $cashlog->mt4_id = $userinfo->mt4_id;
                $cashlog->balance = $res->balance - Input::get('money');


                //用户金额表里减去相应数据
                $res->uid = $id;
                $res->balance -= Input::get('money');
                $res->freeze += Input::get('money'); 
                if($cashlog->save() && $res->save()){
                    $data['status'] = 1;
                    $data['msg'] = "操作成功，请等待...";
                }
            }else{
                $data['status'] = 3;
                $data['msg'] = '余额不足';
            }
        }else{
            $data['status'] = 2;
            $data['msg'] = '请拥有足够佣金后再尝试';
        }
        
        return json_encode($data);
    }

    /**
     * 获取用户未提现佣金
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getRecord(Request $request)
    {   
        $id = $request->input('id');
        $cashlog = new cashlog;
        $result = $cashlog->where('uid',$id)->get(); //获取未确认的用户提现信息
        // $request = $request->all();
        if($result){
            return view('share.txjl',['data'=>$result]);
        }
    }

    /**
     * 获取佣金排行榜(需加redis优化)
     */
    public function getChart(){

        //获取昨天早上5点的时间戳
        $start_time = mktime(5,0,0,date('m'),date('d')-1,date('Y')); 
        //获取今日5点5分的时间戳
        $end_time = mktime(5,5,0,date('m'),date('d'),date('Y')); 
        $top = top::whereBetween('add_time',[$start_time,$end_time])->orderBy('cumulate','desc')->get();
        $sum = round(cash::sum('cumulate'),2);
        $data = [];
        $data = $top;
        foreach ($top as $k => $v) {
            $data[$k]['pid'] = Users::select('utel')->find($v['uid'])['utel'];
        }
        // if(Redis::exists('data') && Redis::exists('sum')){
        //     $data = Redis::get('data');
        //     $sum = Redis::get('sum');
        //     return view('share.yjphb',['data'=>unserialize($data),'sum'=>unserialize($sum)]);
        // }else{
        //     $banders = bander::select('pid')->groupBy('pid')->get();    //获取所有代理id
        //     $data = [];
        //     $tmp = [];
        //     $sum = 0;   //总佣金
        //     foreach ($banders as $kk=> $vv){
        //         $result = bander::where('pid',$vv['pid'])->get();  //获取该用户的子用户
        //         //拼装信息
        //         $tmp['cumulate'] = 0;
        //         foreach ($result as $k =>$v) {
        //             $tmp['records'][$k] = $this->getRecordById($v['bid']);   //获取子用户交易产生的佣金
        //             $tmp['cumulate'] += $tmp['records'][$k];//累积获得佣金
        //         }
        //         $data[$kk]['pid'] = Users::select('utel')->find($vv['pid'])['utel'];
        //         $data[$kk]['cumulate'] = $tmp['cumulate'];
        //         $sum += $tmp['cumulate'];
        //     }
        //     foreach ($data as $key => $row)
        //     {
        //         $volume[$key]  = $row['cumulate'];
        //     }
        //     array_multisort($volume, SORT_DESC, $data);
        //     if(count($data)>10){
        //         $data = array_slice($data, 0,10);
        //     }
        //     Redis::setex('data',24*3600,serialize($data));
        //     Redis::setex('sum',24*3600,serialize($sum));
            return view('share.yjphb',['data'=>$data,'sum'=>$sum]);
        // }

    }


    /**
     * 加载分享页面
     * @param  [type] $phonecode [description]
     * @return [type]            [description]
     */
    public function shareAll(Request $request)
    {
        $phonecode = $request->input('utel');
        return view('share.share',['bandcode'=>$phonecode,'code'=>encrypt($phonecode)]);
    }

    /**
     * 邀请注册
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function bandShare(Request $request)
    {
        $pid = $request->input('band','');
        $bid = $request->input('phone','');
        $code=$request->input('code','');
        $data = [];
        if(empty($code)){
            $data['success'] = 222;
            $data['message'] = '请填写验证码';
            return json_encode($data);
        }
        if(empty($pid) || empty($bid)){
            $data['success'] = 111;
            $data['message'] = '请填写邀请信息';
            return json_encode($data);
        }
        $ips= new ips();
        $rs=$ips->where('phone',$bid)->where('code',$code)->first();//查询验证码是否发送
        if($rs) {//已经发送验证码
            if((time()-$rs->addtime)>60)
            {
                $data['success'] = 333;
                $data['message'] = '验证码超时';
                return json_encode($data);
            }
            $user = new Users;
            $res = $user->where('utel',$bid)->first();
            if($res){
                $data['success'] = 666;
                $data['message'] = '此手机号已经注册！';
                return json_encode($data);
            }else{
                //添加到客户表
                $users = new Users;
                //获取邀请人信息，复制给被邀请人
                $result = $users->where('utel',$pid)->first();
                if($result){
                    $u = new user;
                    $ptype = $u->where('status',1)->find($result->ptype);
                    if($ptype){
                        $users->ptype = $result->ptype;
                    }else{
                        $users->ptype = 1;
                    }
                    $users->uname = $result->uname.'邀请来的';
                    $users->utel = $bid;
                    $users->telcity = $this->getTelCity($bid);
                    $users->weixin = '';
                    $users->note = $result->note;
                    $users->utm_source = $result->utm_source;
                    $users->utm_term = $result->utm_term;
                    $users->ingod = 0;
                    $users->sumgod = 0;
                    $users->contactnum = 0;
                    $users->contactpeople =$result->contactpeople; 
                    $users->show = 0;
                    $users->show1 = 1;
                    $users->jemail = '';
                    $users->utm_medium =$result->utm_medium;
                    $users->utm_content = $result->utm_content;
                    $users->utm_campaign = $result->utm_campaign;
                    $users->telcity = $this->getTelCity($bid);
                    $users->AddDate = date('Y-m-d H:i:s',time());
                    $users->regsource = 1;
                    $users->ucheck = 1; 
                    $users->IP = $this->getIp();
                    $users->url = $result->url; 
                    $users->reurl = $result->reurl;
                    $users->str1 = $this->getAgent();


                    $msg = new msg;
                    $msg->uid = 1;
                    $msg->uname = '系统管理员';
                    $msg->addtime = date('Y-m-d H:i:s',time());
                    $msg->content = '有新的客户分配到您的名下，请查看！';
                    $msg->status = 1;
                    $msg->type = '系统消息';
                    $msg->juid = $result->ptype;
                    if($users->save()){
                        $bander = new bander;
                        $bander->pid = $result->Id;
                        $bander->bid = $users->select('Id')->where('utel',$bid)->first();
                        $bander->bandTime = time();
                        if($bander->save() && $msg->save()){
                            $data['success'] = 1;
                            $data['message'] = '恭喜您已经成功注册，客服会快速与您取得联系。'; 
                        }else{
                            $data['success'] = 444;
                            $data['message'] = '此链接已经失效，请重新获取邀请链接！';
                        }
                    }else{
                        $data['success'] = 444;
                        $data['message'] = '此链接已经失效，请重新获取邀请链接！';
                    }
                }else{
                    $data['success'] = 444;
                    $data['message'] = '此链接已经失效，请重新获取邀请链接！';
                }  
            }
            
        }
        
        return json_encode($data);
        // dd($data);
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

}
