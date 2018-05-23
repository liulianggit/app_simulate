<?php

namespace App\Http\Controllers\home;

use App\Http\Controllers\common\CommonController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\trade_cate;
use App\model\cates;
use App\model\recommend;
use Illuminate\Support\Facades\DB;

class TradeCateController extends CommonController{
    /**
     * 返回用户交易品种列表
     * @param Request $request
     */
    public function get_user_trade(Request $request)
    {
        $uid = $request->input('uid','');
        if(empty($uid)){
            return $this->jsonOut('2','请传入用户id！');
        }
        $result = trade_cate::where('uid',$uid)->get();
        if(count($result)==0 || !$result){
            $result = trade_cate::where('uid',0)->get();
        }
        $data = $result;
        // return $this->jsonOut('1','获取成功！',$data);
        $tmp = '';
        $cmp = [];
        foreach ($result as $k => $v) {
            $cmp[$k] = $v['symbol'];
        }
        $cmp = array_unique($cmp);
        $tmp = implode($cmp,',');
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'symbol_price_collect';
        $params['symbol']         = $tmp;
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbol/price/collect',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            foreach ($result as $k => $v) {
                $data[$k]['digits'] = cates::select('digits')->where('symbol',$v['symbol'])->first()['digits'];
                $str = $this->symbol_trade_time($v['symbol']);
                if($str && strlen($str)>1){
                    $data[$k]['trade_time'] = 1;
                }else{
                    $data[$k]['trade_time'] = $str;
                }
                foreach ($res['data'] as $key => $value) {

                    if($v['symbol'] == $key){
                        $data[$k]['yesterday_close_price'] = $res['data'][$v['symbol']]['yesterday_close_price'];
                        // $data[$k]['digits'] = $digit['digits'];
                    }
                }

            }
            return $this->jsonOut('1','获取成功！',$data);
        }else{
            return $this->jsonOut('0',$res['message'],$result);
        }

    }

    /**
     * 获取热门推荐的交易品种
     * @return [type] [description]
     */
    public function get_recommend()
    {
        $result = recommend::get();
        $tmp = '';
        if(count($result) >0){
            $data = $result;
            foreach ($result as $k => $v) {
                $tmp[$k] = $v['symbol'];
            }
            $tmp = implode($tmp,',');
            $params['private_key']    = config('app.private_key');
            $params['partner_key']    = config('app.partner_key');
            $params['partner_secret'] = config('app.partner_secret');
            $params['action']         = 'symbol_price_collect';
            $params['symbol']         = $tmp;
            $sign = $this->getSign($params['private_key'], $params); #生成签名
            $params['sign'] = $sign; 
            unset($params['partner_key']); # 销毁加密key
            unset($params['partner_secret']); # 销毁加密key
            $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbol/price/collect',$params);
            $res = json_decode($res,true);
            if($res['is_succ']){
                foreach ($result as $k => $v) {
                    $data[$k]['digits'] = cates::select('digits')->where('symbol',$v['symbol'])->first()['digits'];
                    $str = $this->symbol_trade_time($v['symbol']);
                    if($str && strlen($str)>1){
                        $data[$k]['trade_time'] = 1;
                    }else{
                        $data[$k]['trade_time'] = $str;
                    }
                    foreach ($res['data'] as $key => $value) {

                        if($v['symbol'] == $key){
                            $data[$k]['yesterday_close_price'] = $res['data'][$v['symbol']]['yesterday_close_price'];
                            // $data[$k]['digits'] = $digit['digits'];
                        }
                    }

                }
                return $this->jsonOut('1','返回成功！',$data);
            }else{
                return $this->jsonOut('0',$res['message'],$result);
            }
        }else{
            return $this->jsonOut('0','数据获取失败！');
        }
        
    }

    /**
     * 添加用户自选交易品种(批量)
     * @param Request $requset [description]
     */
    public function add_user_trades(Request $requset)
    {
        $data =$requset->input('data');
        $data = json_decode($data,true);
        $uid =$requset->input('uid');
        /*if(empty($uid) || count($data)==0){
            return $this->jsonOut('2','缺少参数');
        }*/
        if(empty($uid))
        {
            return $this->jsonOut('3','uid为空');
        }
        if(count($data)==0){
            return $this->jsonOut('4','data为空');
        }
        $tmp = trade_cate::where('uid',$uid)->first();
        $res = trade_cate::insert($data);
        DB::beginTransaction();
        try{
            if($tmp){
                $rs = trade_cate::where('uid',$uid)->delete();
                if($rs){
                    $res = trade_cate::insert($data);
                }
            }else{
                $res = trade_cate::insert($data);
            }
            DB::commit();
            return $this->jsonOut('1','添加成功！',$data);
        } catch(\Exception $e){
            DB::rollback();
            return $this->jsonOut('0','添加失败');
        }

    }


  //添加用户自选交易品种(批量) 新加
    public function add_user_trades_andriod(Request $requset)
    {
        $rev =$requset->input('data');
        $rev=json_decode($rev,true);
          $data=$rev;
        if(count($data)==0){
            return $this->jsonOut('4','data为空');
        }
        foreach($rev as $k=>$v)
        {
            $uid=$v['uid'];
        }
        if(empty($uid))
        {
            return $this->jsonOut('3','uid为空');
        }
        trade_cate::where('uid',$uid)->delete();
        $res = trade_cate::insert($data);
        if($res){
                return $this->jsonOut('1','修改成功！',$data);
            }else{
                return $this->jsonOut('0','修改失败！');
        }

    }

    /**
     * 添加用户自选交易品种
     * @param Request $requset [description]
     */
    public function add_user_trade(Request $requset)
    {
        $uid = $requset->input('uid','');
        $symbol = $requset->input('symbol','');
        $symbol_cn = $requset->input('symbol_cn','');
        $leverage = $requset->input('leverage','');
        if(empty($uid) || empty($symbol) || empty($symbol_cn) || empty($leverage)){
            return $this->jsonOut('2','缺少参数!');
        }else{
            $res = trade_cate::where('symbol',$symbol)->where('uid',$uid)->where('leverage',$leverage)->first();
            if($res){
                return $this->jsonOut('3','不能重复添加！');
            }
            $result = trade_cate::where('uid',0)->get();
            $data = [];
            foreach ($result as $k => $v) {
                $tmp = trade_cate::where('symbol',$v['symbol'])->where('uid',$uid)->where('leverage',$v['leverage'])->first();
                if(!$tmp){
                    $data[$k]['uid'] = $uid;
                    $data[$k]['symbol'] = $v['symbol'];
                    $data[$k]['symbol_cn'] = $v['symbol_cn'];
                    $data[$k]['leverage'] = $v['leverage'];
                    
                }
            }
            if(count($data)>0){
                $s = count($data);
                $data[$s]['uid'] = $uid;
                $data[$s]['symbol'] = $symbol;
                $data[$s]['symbol_cn'] = $symbol_cn;
                $data[$s]['leverage'] = $leverage;
            }else{
                $data[0]['uid'] = $uid;
                $data[0]['symbol'] = $symbol;
                $data[0]['symbol_cn'] = $symbol_cn;
                $data[0]['leverage'] = $leverage;
            }
            
            if(trade_cate::insert($data)){
                return $this->jsonOut('1','添加成功');
            }else{
                return $this->jsonOut('0','添加失败');
            }
        }
    }

    /**
     * 删除用户自选交易品种
     * @param  Request $requset [description]
     * @return [type]           [description]
     */
    public function del_user_trade(Request $requset)
    {
        $uid = $requset->input('uid','');
        $symbol = $requset->input('symbol','');
        $leverage = $requset->input('leverage','');
        if($uid == '' || empty($symbol)  || empty($leverage)){
            return $this->jsonOut('2','缺少参数!');
        }else{
            if($uid == 0){
                return $this->jsonOut('3','系统默认，禁止删除！');
            }else{
                $res = trade_cate::where('uid',$uid)->where('symbol',$symbol)->where('leverage',$leverage)->delete();
                if($res){
                    return $this->jsonOut('1','删除成功！');
                }else{
                    return $this->jsonOut('0','删除失败，请重试！');
                }
            }
        }
            
    }


    /**
     * 获取产品交易时间段状态
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function symbol_trade_time($symbol)
    {
        if(cache('data') && count(cache('data'))>0){
            $data = cache('data');
            if(array_key_exists($symbol, $data)){
                $tmp = $data[$symbol][date('w')];
                $tmp = explode(',',$tmp);
                $tmp = $tmp[0];
                if($tmp=='0:0-0:0'){
                    return 0;
                }else{
                    $tmp = explode('-',$tmp);
                    $tmp1 = explode(':',$tmp[0]);
                    $tmp2 = explode(':',$tmp[1]);
                    if($tmp1[0]+5>24){
                        $t = $tmp1[0]+5-24;
                        // $time = mktime($t,$tmp1[1],0,date('m'),date('d')+1,date('Y'));
                    }else{
                        $t = $tmp1[0]+5;
                    }  
                    $time = mktime($t,$tmp1[1],0,date('m'),date('d'),date('Y'));
                    if($tmp2[0]+5>24){
                        $t1 = $tmp2[0]+5-24;
                        // $time1 = mktime($t1,$tmp2[1],0,date('m'),date('d')+1,date('Y'));
                    }else{
                        $t1 = $tmp2[0]+5;
                    } 
                    $time1 = mktime($t1,$tmp2[1],0,date('m'),date('d'),date('Y'));
                    if(time()>=$time || time()<=$time1){
                        return 1;
                    }else{
                        return 0;
                    }

                }

            }else{
                return 1;
            }
        }else{
            $params['private_key']    = config('app.private_key');
            $params['partner_key']    = config('app.partner_key');
            $params['partner_secret'] = config('app.partner_secret');
            $params['action']         = 'symbol_trade_time';
            $sign = $this->getSign($params['private_key'], $params); #生成签名
            $params['sign'] = $sign; 
            unset($params['partner_key']); # 销毁加密key
            unset($params['partner_secret']); # 销毁加密key
            $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbol/trade_time',$params);
            $res = json_decode($res,true);
            if($res['is_succ']){
                $data = $res['data'];
                cache(['data'=>$data],60);
                $this->symbol_trade_time($symbol);
            }else{
                return $res['message'];
            }   
        }
    }
}
