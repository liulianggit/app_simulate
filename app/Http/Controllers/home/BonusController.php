<?php
namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\Users;
use App\model\asset;
use App\model\userinfo;
use App\model\webconfig;
use App\Http\Controllers\common\CommonController;

class BonusController extends CommonController
{
	/**
     * 红包列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_bonus(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $limit = $request->input('limit');
        $limit = $this->get_input($limit);
        $offset = $request->input('offset');
        $offset = $this->get_input($offset);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id');
        }else if(empty($limit)){
            return $this->jsonOut('4','请输入要查询的条数');
        }else if($offset == ''){
            return $this->jsonOut('5','请输入偏移量');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'bonus_index';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['limit'] = $limit;
                $params['offset'] = $offset;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/bonus/index',$params);
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
     * 领取红包
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function bonus_receive(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $id = $request->input('id');
        $id = $this->get_input($id);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id');
        }else if(empty($id)){
            return $this->jsonOut('4','请传入红包id');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'bonus_receive';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['id'] = $id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('put','https://demo.tigerwit.com/api/third/bonus/receive',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    return $this->jsonOut('1','返回成功！');
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
     * 我的红包列表
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function user_bonus(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $limit = $request->input('limit')?$request->input('limit'):10;
        $limit = $this->get_input($limit);
        $offset = $request->input('offset');
        $offset = $this->get_input($offset);
        $num = $request->input('num')?$request->input('num'):0;
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id');
        }else if($offset == ''){
            return $this->jsonOut('6','请输入偏移量');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'bonus';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['limit'] = $limit;
                $params['offset'] = $offset;
                $params['type'] = 1;    //已领取，可兑换
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/bonus',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    $pams['private_key']    = config('app.private_key');
                    $pams['partner_key']    = config('app.partner_key');
                    $pams['partner_secret'] = config('app.partner_secret');
                    $pams['action']         = 'bonus';
                    $pams['user_id']        = $uid;
                    $pams['mt4_id'] = $info->mt4_id;
                    $pams['offset'] = $num;
                    if($limit == count($res['data']['records'])){
                        $pams['limit'] = $limit;
                    }else{
                        $pams['limit'] = $limit-count($res['data']['records']);
                        $num += $pams['limit'];
                    }
                    $pams['type'] = 2;    //已兑换,已过期,已失效
                    $sign1 = $this->getSign($pams['private_key'], $pams);
                    $pams['sign'] = $sign1; 
                    unset($pams['partner_key']); # 销毁加密key
                    unset($pams['partner_secret']); # 销毁加密key
                    $res1 = $this->curl_request('get','https://demo.tigerwit.com/api/third/bonus',$pams);
                    $res1 = json_decode($res1,true);
                    if($res1['is_succ']){
                        $data['record_count'] = $res['data']['record_count']+$res1['data']['record_count'];
                        $data['page_count'] = ceil($data['record_count']/$limit);
                        if($limit == count($res['data']['records'])){
                            $data['records'] = $res['data']['records'];
                        }else{
                            $data['records'] = array_merge($res['data']['records'],$res1['data']['records']);
                        }
                        $data['num'] = $num;
                        foreach ($data['records'] as $k=>$v) {
                            $data['records'][$k]['acquire_time'] = date('Y-m-d H:i:s',$v['acquire_time']);
                            $data['records'][$k]['valid_end'] = date('Y-m-d H:i:s',$v['valid_end']);
                        }
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
     * 兑换红包
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function bonus_exchange(Request $request)
    {
        $uid = $request->input('uid');
        $uid = $this->get_input($uid);
        $id = $request->input('id');
        $id = $this->get_input($id);
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id');
        }else if(empty($id)){
            return $this->jsonOut('4','请传入红包id');
        }

        $rs = Users::find($uid);
        if($rs){
            $info = userinfo::where('uid',$uid)->first();
            if(!empty($info->mt4_id)){
                $params['private_key']    = config('app.private_key');
                $params['partner_key']    = config('app.partner_key');
                $params['partner_secret'] = config('app.partner_secret');
                $params['action']         = 'bonus_exchange';
                $params['user_id']        = $uid;
                $params['mt4_id'] = $info->mt4_id;
                $params['id'] = $id;
                $sign = $this->getSign($params['private_key'], $params); #生成签名
                $params['sign'] = $sign; 
                unset($params['partner_key']); # 销毁加密key
                unset($params['partner_secret']); # 销毁加密key
                $res = $this->curl_request('post','https://demo.tigerwit.com/api/third/bonus/exchange',$params);
                $res = json_decode($res,true);
                if($res['is_succ']){
                    return $this->jsonOut('1',$res['message']);
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