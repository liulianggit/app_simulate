<?php
namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\model\Users;
use App\model\asset;
use App\model\userinfo;
use App\model\webconfig;
use App\model\trade_cate;
use App\model\margin;
use App\model\lev;
use App\model\symbols;
use App\Http\Controllers\common\CommonController;

class SymbolController extends CommonController
{
	/**
     * 获取交易品种分组
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_symbols(Request $request)
    {
        $uid = $request->input('uid');
        if(empty($uid) || is_null($uid)){
            return $this->jsonOut('2','请传入uid');
        }
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'v2_symbols';
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/v2/symbols',$params);
        $res = json_decode($res,true);
        $cates = trade_cate::where('uid',$uid)->get();//获取用户自定义交易品种
        if(count($cates)==0){
            $cates = trade_cate::where('uid',0)->get();
        }
        // return $this->jsonOut('2','请传入uid',$res['data']);
        
        if($res['is_succ']){
            $forex = $res['data']['forex'];  //货币对
            $energy = $res['data']['energy'];     //原油
            $metal = $res['data']['metal'];      //黄金白银
            $cfd = $res['data']['cfd'];    //股指
            foreach ($forex as $k => $v) {
                $res['data']['forex'][$k]['leve200'] = '0';
                $res['data']['forex'][$k]['leve100'] = '0';
                $res['data']['forex'][$k]['leve50'] = '0';
            }
            foreach ($energy as $k => $v) {
                $res['data']['energy'][$k]['leve200'] = '0';
                $res['data']['energy'][$k]['leve100'] = '0';
                $res['data']['energy'][$k]['leve50'] = '0';
            }
            foreach ($metal as $k => $v) {
                $res['data']['metal'][$k]['leve200'] = '0';
                $res['data']['metal'][$k]['leve100'] = '0';
                $res['data']['metal'][$k]['leve50'] = '0';
            }
            foreach ($cfd as $k => $v) {
                $res['data']['cfd'][$k]['leve200'] = '0';
                $res['data']['cfd'][$k]['leve100'] = '0';
                $res['data']['cfd'][$k]['leve50'] = '0';
            }
            $data = $res['data'];

            foreach ($cates as $key => $value) {

               foreach ($forex as $k => $v) {
                    if($value['symbol']==$v['symbol']){
                        if($value['leverage']==200){
                            $data['forex'][$k]['leve200'] = '1';
                        }elseif ($value['leverage']==100) {
                            $data['forex'][$k]['leve100'] = '1';
                        }else{
                            $data['forex'][$k]['leve50'] = '1';
                        }
                    }
                }
                foreach ($energy as $k => $v) {
                    if($value['symbol']==$v['symbol']){
                        if($value['leverage']==50){
                            $data['energy'][$k]['leve50'] = '1';
                        }else if($value['leverage'] == 100){
                            $data['energy'][$k]['leve100'] = '1';
                        }else{
                            $data['energy'][$k]['leve200'] = '1';
                        }
                    }
                }
                foreach ($metal as $k => $v) {
                    if($value['symbol']==$v['symbol']){
                        if($value['leverage']==50){
                            $data['metal'][$k]['leve50'] = '1';
                        }else if($value['leverage'] == 100){
                            $data['metal'][$k]['leve100'] = '1';
                        }else{
                            $data['metal'][$k]['leve200'] = '1';
                        }
                    }
                }
                foreach ($cfd as $k => $v) {
                    if($value['symbol']==$v['symbol']){
                        if($value['leverage']==50){
                            $data['cfd'][$k]['leve50'] = '1';
                        }else if($value['leverage'] == 100){
                            $data['cfd'][$k]['leve100'] = '1';
                        }else{
                            $data['cfd'][$k]['leve200'] = '1';
                        }
                    }
                }
            }
            
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }

    /**
     * 搜索交易品种
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function search_symbol(Request $request)
    {
        $symbol = $request->input('symbol','');
        $uid = $request->input('uid');
        if(empty($uid) || is_null($uid)){
            return $this->jsonOut('2','请传入uid');
        }
        if($symbol ==''){
            return $this->jsonOut('3','请输入要查询的内容');
        }
        $res = symbols::where('symbol','like','%'.$symbol.'%')->orWhere('symbol_cn','like','%'.$symbol.'%')->get();

        $data = [];
        if(count($res)>0){
            $cates = trade_cate::where('uid',$uid)->get();//获取用户自定义交易品种
            if(count($cates)==0){
                $cates = trade_cate::where('uid',0)->get();
            }
            foreach ($res as $key => $value) {
                $data[$key]['symbol'] = $value['symbol'];
                $data[$key]['symbol_cn'] = $value['symbol_cn'];
                $data[$key]['leve50'] = '0';
                $data[$key]['leve100'] = '0';
                $data[$key]['leve200'] = '0';
                $data[$key]['symbol_type'] = $value['symbol_type'];
                
                foreach ($cates as $k => $v) {
                    if($v['symbol'] == $value['symbol']){
                        if($v['leverage'] == 50){
                            $data[$key]['leve50'] = '1';
                        }elseif ($v['leverage'] ==100) {
                            $data[$key]['leve100'] = '1';
                        }else{
                            $data[$key]['leve200'] = '1';
                        }
                    }
                }
            }
            
            return $this->jsonOut('1','搜索成功',$data);
        }else{
            return $this->jsonOut('1','搜索成功',$data);
        }
        
        // return $this->jsonOut('2','请传入uid',$res['data']);
        
        if($res['is_succ']){
            $forex = $res['data']['forex'];  //货币对
            $energy = $res['data']['energy'];     //原油
            $metal = $res['data']['metal'];      //黄金白银
            $cfd = $res['data']['cfd'];    //股指
            foreach ($forex as $k => $v) {
                $res['data']['forex'][$k]['leve200'] = '0';
                $res['data']['forex'][$k]['leve100'] = '0';
                $res['data']['forex'][$k]['leve50'] = '0';
            }
            foreach ($energy as $k => $v) {
                $res['data']['energy'][$k]['leve200'] = '0';
                $res['data']['energy'][$k]['leve100'] = '0';
                $res['data']['energy'][$k]['leve50'] = '0';
            }
            foreach ($metal as $k => $v) {
                $res['data']['metal'][$k]['leve200'] = '0';
                $res['data']['metal'][$k]['leve100'] = '0';
                $res['data']['metal'][$k]['leve50'] = '0';
            }
            foreach ($cfd as $k => $v) {
                $res['data']['cfd'][$k]['leve200'] = '0';
                $res['data']['cfd'][$k]['leve100'] = '0';
                $res['data']['cfd'][$k]['leve50'] = '0';
            }
            $data = $res['data'];

            foreach ($cates as $key => $value) {

               foreach ($forex as $k => $v) {
                    if($value['symbol']==$v['symbol']){
                        if($value['leverage']==200){
                            $data['forex'][$k]['leve200'] = '1';
                        }elseif ($value['leverage']==100) {
                            $data['forex'][$k]['leve100'] = '1';
                        }else{
                            $data['forex'][$k]['leve50'] = '1';
                        }
                    }
                }
                foreach ($energy as $k => $v) {
                    if($value['symbol']==$v['symbol']){
                        if($value['leverage']==50){
                            $data['energy'][$k]['leve50'] = '1';
                        }else if($value['leverage'] == 100){
                            $data['energy'][$k]['leve100'] = '1';
                        }else{
                            $data['energy'][$k]['leve200'] = '1';
                        }
                    }
                }
                foreach ($metal as $k => $v) {
                    if($value['symbol']==$v['symbol']){
                        if($value['leverage']==50){
                            $data['metal'][$k]['leve50'] = '1';
                        }else if($value['leverage'] == 100){
                            $data['metal'][$k]['leve100'] = '1';
                        }else{
                            $data['metal'][$k]['leve200'] = '1';
                        }
                    }
                }
                foreach ($cfd as $k => $v) {
                    if($value['symbol']==$v['symbol']){
                        if($value['leverage']==50){
                            $data['cfd'][$k]['leve50'] = '1';
                        }else if($value['leverage'] == 100){
                            $data['cfd'][$k]['leve100'] = '1';
                        }else{
                            $data['cfd'][$k]['leve200'] = '1';
                        }
                    }
                }
            }
            
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }

    

    /**
     * 历史报价
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_quote_history(Request $request)
    {
        $params = $request->except('start_time','sign_key');
        foreach ($params as $key => $value) {
            if($value == ''){
                return $this->jsonOut('2','请传入必要参数');
            }
        }
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'quote_history';
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/quote/history',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            foreach($data['records'] as $k=>$v){
                $data['records'][$k]['time'] = date('Y-m-d H:i:s',$v['time']);
            }
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }

    /**
     * 获取交易品种汇总
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_all_symbols(Request $request)
    {
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'symbols';
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbols',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }

    /**
     * 获取单个交易品种详情
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_symbols_info(Request $request)
    {
        $symbol = $request->input('symbol');    //交易品种，英文名
        if(empty($symbol)){
            return $this->jsonOut('2','请传入查询的交易品种！');
        }
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'symbol_info';
        $params['symbol']         = $symbol;
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbol/info',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }

    /**
     * 获取所有交易品种详情
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function all_symbols_info(Request $request)
    {
        
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'symbols';
        $params['detail']         = 1;  //固定传值
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbols',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }

    // public function insert_symbols()
    // {
        // $params['private_key']    = config('app.private_key');
        // $params['partner_key']    = config('app.partner_key');
        // $params['partner_secret'] = config('app.partner_secret');
        // $params['action']         = 'symbols';
        // $params['detail']         = 1;  //固定传值
        // $sign = $this->getSign($params['private_key'], $params); #生成签名
        // $params['sign'] = $sign; 
        // unset($params['partner_key']); # 销毁加密key
        // unset($params['partner_secret']); # 销毁加密key
        // $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbols',$params);
        // $res = json_decode($res,true);
        // if($res['is_succ']){
        //     $data = $res['data'];
        //     $tmp = [];
        //     foreach ($data as $k => $v) {
        //         $tmp[$k]['symbol'] = $v['symbol'];
        //         $tmp[$k]['margin_divider'] = $v['margin_divider'];
        //         $tmp[$k]['margin_initial'] = $v['margin_initial'];
        //     }
        //     margin::insert($tmp);
        // }else{
        //     return $this->jsonOut('4',$res['message']);
        // }
    // }
    // public function insert_symbols()
    // {
    //     $res = lev::where('symbol','like','%'.'CADJPY'.'%')->first();
    //     return $this->jsonOut('1','as',$res);
    // }
    /**
     * 获取单个交易品种详情【交易品种详情页面】
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_symbol_detail(Request $request)
    {
        
        $symbol = $request->input('symbol');    //交易品种，英文名
        if(empty($symbol)){
            return $this->jsonOut('2','请传入查询的交易品种！');
        }
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'symbol_detail';
        $params['symbol']         = $symbol;
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbol/detail',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }

     /**
     * 获取单个品种开收盘信息
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function get_symbol_price(Request $request)
    {
        
        $symbol = $request->input('symbol');    //交易品种，英文名
        if(empty($symbol)){
            return $this->jsonOut('2','请传入查询的交易品种！');
        }
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'symbol_price';
        $params['symbol']         = $symbol;
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbol/price',$params);
        $res = json_decode($res,true);
        $pams['private_key']    = config('app.private_key');
        $pams['partner_key']    = config('app.partner_key');
        $pams['partner_secret'] = config('app.partner_secret');
        $pams['action']         = 'symbol_info';
        $pams['symbol']         = $symbol;
        $sign = $this->getSign($pams['private_key'], $pams); #生成签名
        $pams['sign'] = $sign; 
        unset($pams['partner_key']); # 销毁加密key
        unset($pams['partner_secret']); # 销毁加密key
        $res1 = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbol/info',$pams);
        $res1 = json_decode($res1,true);
        if($res['is_succ'] && $res1['is_succ']){
            $data = array_merge($res['data'],$res1['data']);
            $data['symbol_type'] = symbols::where('symbol','like',substr($symbol, 0,6).'%')->first()['symbol_type'];
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
        if($res['is_succ']){
            $data = $res['data'];
            $data['symbol_type'] = symbols::where('symbol','like',substr($symbol, 0,6).'%')->first()['symbol_type'];
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }

    /**
     * 获取单多个品种开收盘信息
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function symbol_price_collect(Request $request)
    {
        
        $symbol = $request->input('symbol');    //交易品种，英文名
        if(empty($symbol)){
            return $this->jsonOut('2','请传入查询的交易品种！');
        }
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'symbol_price_collect';
        $params['symbol']         = $symbol;
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbol/price/collect',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }

    /**
     * 获取单个价格信息
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function symbol_now_price(Request $request)
    {
        
        $symbol = $request->input('symbol');    //交易品种，英文名
        if(empty($symbol)){
            return $this->jsonOut('2','请传入查询的交易品种！');
        }
        $params['private_key']    = config('app.private_key');
        $params['partner_key']    = config('app.partner_key');
        $params['partner_secret'] = config('app.partner_secret');
        $params['action']         = 'symbol_now_price';
        $params['symbol']         = $symbol;
        $sign = $this->getSign($params['private_key'], $params); #生成签名
        $params['sign'] = $sign; 
        unset($params['partner_key']); # 销毁加密key
        unset($params['partner_secret']); # 销毁加密key
        $res = $this->curl_request('get','https://demo.tigerwit.com/api/third/symbol/now_price',$params);
        $res = json_decode($res,true);
        if($res['is_succ']){
            $data = $res['data'];
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }

    /**
     * 获取产品交易时间段
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function symbol_trade_time(Request $request)
    {
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
            return $this->jsonOut('1','返回成功！',$data);
        }else{
            return $this->jsonOut('4',$res['message']);
        }
    }
}