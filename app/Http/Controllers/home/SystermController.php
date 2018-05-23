<?php

namespace App\Http\Controllers\home;
use App\Http\Controllers\common\CommonController;
use App\model\notview;
use App\model\sysview;
use Illuminate\Http\Request;
use App\model\systerm;
use App\model\notice;
use App\model\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Concerns\InteractsWithInput;

class SystermController extends CommonController
{
    /**
     * app系统消息
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function sys_msg(Request $request)
    {
        $uid=$request->input('uid');
        if(empty($uid))
        {
            return $this->jsonOut('3', '请传入用户的id');
        }
        $offset=$request->input('offset');
        if($offset=='')
        {
            return $this->jsonOut('2', '请传入偏移量');
        }
        $limit=$request->input('limit');
        if(!$limit)
        {
            $limit=10;
        }
        $users= new Users();
        $utime=$users->where('id',$uid)->get(['AddDate'])->toArray();
        $utime=strtotime($utime[0]['AddDate']);
        $sys = new systerm();//示例化轮播图表
        //查询需要的数据
        $data = $sys->where('is_top',1)->where('add_time','>',$utime)->offset($offset)->limit($limit)->orderBy('add_time', 'desc')->get(['title', 'short_content', 'add_time'])->toArray();
        $record_count=$sys->where('is_top',1)->where('add_time','>',$utime)->count();
        $page_count=ceil($record_count/$limit);
        if (!$data) {
            $data=[];
        }
        //拼装数组
            foreach ($data as $k => $v) {
                $v['add_time']=date("Y-m-d H:i:s",$v['add_time']);
                $data[$k] = $v;
            }
            $data1['record_count']=$record_count;
            $data1['page_count']=$page_count;
            $data1['records']=$data;
            //写入信息浏览表
            $sysview= new sysview();
            $view=$sysview->where("uid",$uid)->first();
            if($view)
            {
                $view->add_time=time();
                $view->save();
            }else{
                $sysview->uid=$uid;
                $sysview->add_time=time();
                $sysview->save();
           }
            return $this->jsonOut('1', '获取成功', $data1);

    }

    /**
     * app交易提醒
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function trade_notice(Request $request)
    {
        $uid=$request->input('uid');
        if(empty($uid))
        {
            return $this->jsonOut('3', '请传入用户的id');
        }
        $offset=$request->input('offset');
        if($offset=='')
        {
            return $this->jsonOut('2', '请传入偏移量');
        }
        $limit=$request->input('limit');
        if(!$limit)
        {
            $limit=10;
        }
        $notice= new notice();
        //查询需要的数据
        $data = $notice->where('uid',$uid)->offset($offset)->limit($limit)->orderBy('add_time', 'desc')->get()->toArray();
        $record_count=$notice->where('uid',$uid)->count();
        $page_count=ceil($record_count/$limit);
        if (!$data)
        {
            $data=[];
        }

            //拼装数组
            foreach ($data as $k => $v) {
                if(!$v['money'])
                {
                    $v['money']='';
                }
                if(!$v['order_no'])
                {
                    $v['order_no']='';
                }
                if(!$v['trade_type'])
                {
                    $v['trade_type']='';
                }
                if(!$v['content'])
                {
                    $v['content']='';
                }
                if(!$v['order_direct'])
                {
                    $v['order_direct']='';
                }
                if(!$v['order_num'])
                {
                    $v['order_num']='';
                }
                if(!$v['explain'])
                {
                    $v['explain']='';
                }
                if(!$v['state'])
                {
                    $v['state']='';
                }
                $v['add_time']=date("Y-m-d H:i:s",$v['add_time']);
                $data[$k] = $v;
            }
            $data1['record_count']=$record_count;
            $data1['page_count']=$page_count;
            $data1['records']=$data;
        //写入信息浏览表
        $notview= new notview();
        $view=$notview->where("uid",$uid)->first();
        if($view)
        {
            $view->add_time=time();
            $view->save();
        }else{
            $notview->uid=$uid;
            $notview->add_time=time();
            $notview->save();
        }
            return $this->jsonOut('1', '获取成功', $data1);

    }
   //根据时间戳获取星期
    public function week($time)
    {

        if(is_numeric($time))
        {
            $weekday = array('周日','周一','周二','周三','周四','周五','周六');
        }
        return $weekday[date('w', $time)];

    }


}
