<?php

namespace App\Http\Controllers\home;
use App\Http\Controllers\common\CommonController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\advert;
use App\model\slide;
use App\model\idea;
use App\model\userinfo;
use App\model\Users;
use App\model\content;
use App\model\column;
use App\model\rumen;
use App\model\systerm;
use App\model\sysview;
use App\model\notice;
use App\model\notview;
use App\model\zhibiao;
use App\aliyun\api_demo\SmsDemo;
use Illuminate\Http\Concerns\InteractsWithInput;

class AdvertController extends CommonController
{

    //返回json数据
    public function jsonOut1($code,$is_show,$message,$result=NULL){
        $data['code']=$code;//错误码
        $data['is_show']=$is_show;
        $data['message']=urlencode($message);//错误信息
        $data['data']=$result;//返回的数据
        return urldecode(json_encode($data,true));
        exit;
    }

    /**
     * app轮播图
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function slide(Request $request)
    {
        $slide = new slide();//示例化轮播图表
        //查询需要的数据
        $data = $slide->where('up_time', '<', time())->where('down_time', '>', time())->orderBy('group', 'desc')->get(['picture', 'title', 'url','content']);
        if ($data) {
            //拼装数组
            foreach ($data as $k => $v) {
                if ($v['picture']) {
                    $v['picture'] = "https://crm.pandafx.com/Uploads/Public/" . $v['picture'];//图片加上全路劲
                }

                $data[$k] = $v;
            }

            return $this->jsonOut('1', '获取成功', $data);
        } else {
            return $this->jsonOut('1', '暂无数据');
        }
    }

    /**
     * app弹窗广告
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function advert(Request $request)
    {
        $advert = new advert();
        $data = $advert->where('up_time', '<', time())->where('down_time', '>', time())->first(['picture', 'title', 'url']);
        if ($data) {
            $data['picture']="https://crm.pandafx.com" ."/Uploads/Public/". $data['picture'];
            return $this->jsonOut1('1','1', '获取成功', $data);
        } else {
            return $this->jsonOut1('1','0', '暂无数据');
        }
    }

    /**
     * 测试app
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function ad(Request $request)
    {
        //发送短信
       /* $phone=15811268507;
        $data1='h1';
        $data2='';
        $psd='12345678';
        SmsDemo::sendSms3($phone,$data1,$data2,$psd);*/
        //根据时间戳获取星期
        $time=strtotime('2018-4-8');
        if(is_numeric($time))
        {
            $weekday = array('周日','周一','周二','周三','周四','周五','周六');
            return $weekday[date('w', $time)];
        }

    }

    /**
     * app意见反馈
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function idea(Request $request)
    {
        //获取uid
        $uid = $request->input('uid');
        $mail = $request->input('mail');
        $other= $request->input('other');
        $uid = $this->get_input($uid);
        if (empty($uid)) {
            return $this->jsonOut('2', '请传入用户id！');
        }
        $content = $request->input('content');
        if(empty($content))
        {
            return $this->jsonOut('3', '请传入反馈内容！');
        }
        if($mail)
        {
            $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
            if (!preg_match( $pattern,$mail ))
            {
                return $this->jsonOut('4', '邮箱格式不正确！');
            }

        }
        $idea= new idea();
        $idea->uid=$uid;
        $idea->content=$content;
        $idea->mail=$mail;
        $idea->other=$other;
        $idea->add_time=time();
        if($idea->save()){
            return $this->jsonOut('1','提交成功！');
        }else{
            return $this->jsonOut('0','提交失败！');
        }
    }


    /**
     * 获取用户的开户状态
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function is_true(Request $request)
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
                $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/v2/centre/asset',$pams);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $data['profit'] = $res['data']['balance']+$res['data']['wallet_balance'];//余额
                    if($info->is_true == 1){
                        $data['mt4_id'] = $info->mt4_id;
                    }else{
                        $data['mt4_id'] = $info->demo_mt4;
                    }
                    //获取小红点提示
                      //系统消息
                    $sysview=sysview::where("uid",$uid)->first();
                    if($sysview)
                    {
                        $sys_add_time=$sysview->add_time;
                    }else{
                        $sys_add_time=0;
                    }
                    //交易消息
                    $notview=notview::where("uid",$uid)->first();
                    if($notview)
                    {
                        $not_add_time=$notview->add_time;
                    }else{
                        $not_add_time=0;
                    }
                    $sys_msg=systerm::where('add_time','>',$sys_add_time)->count();
                    $notice=notice::where('add_time','>',$not_add_time)->count();
                    if($sys_msg &&!$notice)
                    {
                        $data['have_news']=1;
                    }elseif(!$sys_msg && $notice){
                        $data['have_news']=2;
                    }elseif($sys_msg && $notice)
                    {
                        $data['have_news']=3;
                    }else{
                        $data['have_news']=0;
                    }
                    $data['is_true'] = $info['is_true'] ? strval($info['is_true']) : '0';
                    return $this->jsonOut('1','返回成功！',$data);
                }else{
                    return $this->jsonOut('7',$res['message']);
                }
            }else{
                $data['is_true'] = $info['is_true'] ? strval($info['is_true']) : '0';
                return $this->jsonOut('1','返回成功！',$data);
            }
        }else{
            return $this->jsonOut('0','没有此用户！');
        }

    }

    /**
     * 文章列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function app_content(Request $request)
    {
        $page=$request->input('page');
        if(!$page)
        {
            $page=1;
        }
        $limit=$request->input('limit');
        if(!$limit)
        {
            $limit=10;
        }
        $offset=$limit*$page;
        $data = content::offset($offset)->limit($limit)->orderBy('add_time', 'desc')->get(['id','cid','title','photo','add_time']);
        $record_count=content::count();
        $page_count=ceil($record_count/$limit);
        if($data) {
            foreach ($data as $k => $v) {
                $column = column::where("id", $v['cid'])->first()->toArray();
                $v['column_title'] = $column['title'];
                if ($v['photo']) {
                    $v['photo'] = "https://crm.pandafx.com/Uploads/Public/" . $v['photo'];
                } else {
                    $v['photo'] = "https://markets.pandafx.com/content/img/default.jpg";
                }
                $v['friend_time'] = $this->friendTime($v['add_time']);
                $v['short_time'] = date("Y.m.d", $v['add_time']);
                $v['detail_url'] = "https://markets.pandafx.com/new_content?id=" . $v['id'];
                $data[$k] = $v;
            }
        }else{
           $data=[];
        }
        $data1['record_count']=$record_count;
        $data1['page_count']=$page_count;
        $data1['record']=$data;
        return $this->jsonOut('1','获取成功！',$data1);

    }

    //友好时间
    public  function friendTime($sTime,$type = 'mohu',$alt = 'false') {
        if (!$sTime)
            return '';
        //sTime=源时间，cTime=当前时间，dTime=时间差
        $cTime      =   time();
        $dTime      =   $cTime - $sTime;
        $dDay       =   intval(date("z",$cTime)) - intval(date("z",$sTime));
        //$dDay     =   intval($dTime/3600/24);
        $dYear      =   intval(date("Y",$cTime)) - intval(date("Y",$sTime));
        //normal：n秒前，n分钟前，n小时前，日期
        if($type=='normal'){
            if( $dTime < 60 ){
                if($dTime < 10){
                    return '刚刚';    //by yangjs
                }else{
                    return intval(floor($dTime / 10) * 10)."秒前";
                }
            }elseif( $dTime < 3600 ){
                return intval($dTime/60)."分钟前";
                //今天的数据.年份相同.日期相同.
            }elseif( $dYear==0 && $dDay == 0  ){
                //return intval($dTime/3600)."小时前";
                return '今天'.date('H:i',$sTime);
            }elseif($dYear==0){
                return date("m月d日 H:i",$sTime);
            }else{
                return date("Y-m-d H:i",$sTime);
            }
        }elseif($type=='mohu'){
            if( $dTime < 60 ){
                return $dTime."秒前";
            }elseif( $dTime < 3600 ){
                return intval($dTime/60)."分钟前";
            }elseif( $dTime >= 3600 && $dDay == 0  ){
                return intval($dTime/3600)."小时前";
            }elseif( $dDay > 0 && $dDay<=7 ){
                return intval($dDay)."天前";
            }elseif( $dDay > 7 &&  $dDay <= 30 ){
                return intval($dDay/7) . '周前';
            }elseif( $dDay > 30 ){
                return intval($dDay/30) . '个月前';
            }
            //full: Y-m-d , H:i:s
        }elseif($type=='full'){
            return date("Y-m-d , H:i:s",$sTime);
        }elseif($type=='ymd'){
            return date("Y-m-d",$sTime);
        }else{
            if( $dTime < 60 ){
                return $dTime."秒前";
            }elseif( $dTime < 3600 ){
                return intval($dTime/60)."分钟前";
            }elseif( $dTime >= 3600 && $dDay == 0  ){
                return intval($dTime/3600)."小时前";
            }elseif($dYear==0){
                return date("Y-m-d H:i:s",$sTime);
            }else{
                return date("Y-m-d H:i:s",$sTime);
            }
        }
    }

    /**
     * 外汇入门列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function app_rumen(Request $request)
    {

        $data = rumen::orderBy('id', 'asc')->get(['id', 'title', 'picture_url', 'turn_url']);
        if ($data) {
             foreach($data as $k=>$v)
             {
                 $v['picture_url']="https://markets.pandafx.com".$v['picture_url'];
                 $data[$k]=$v;
             }
            return $this->jsonOut('1', '获取成功！', $data);
        }else{
            return $this->jsonOut('1', '暂无数据！');
        }
    }

    /**
     * 技术指标列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function app_zhibiao(Request $request)
    {

        $data = zhibiao::orderBy('id', 'asc')->get(['id', 'title', 'picture_url', 'turn_url']);
        if ($data) {
            foreach($data as $k=>$v)
            {
                $v['picture_url']="https://markets.pandafx.com".$v['picture_url'];
                $data[$k]=$v;
            }
            return $this->jsonOut('1', '获取成功！', $data);
        }else{
            return $this->jsonOut('1', '暂无数据！');
        }
    }
}
